<?php
declare(strict_types=1);

/**
 * Dorfaki - Admin Authentication
 *
 * Session-based authentication for the admin panel.
 * Default credentials: username = admin, password = admin
 */

// Admin credentials (username => password_hash)
const ADMIN_USERNAME = 'admin';
// bcrypt hash of "admin" (generated via password_hash('admin', PASSWORD_DEFAULT))
const ADMIN_PASSWORD_HASH = '$2y$12$NLtqei.yWscCa55S./fwtefYcThGWEbOa7IKljiixAg8HPL5DT2oW';

const ADMIN_SESSION_KEY = 'admin_user';
const ADMIN_LOGIN_URL = '/admin/login';
const ADMIN_BASE_URL = '/admin';

/**
 * Start the session if it hasn't been started yet.
 */
function adminStartSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
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
 *
 * @return bool
 */
function adminLogin(string $username, string $password): bool
{
    if (!hash_equals(ADMIN_USERNAME, $username)) {
        return false;
    }

    return password_verify($password, ADMIN_PASSWORD_HASH);
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
