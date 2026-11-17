<?php
declare(strict_types=1);

/**
 * Dorfaki Admin — Logout Handler
 */

require_once __DIR__ . '/../../auth.php';
adminStartSession();
adminLogout();

header('Location: /admin/login', true, 302);
exit;
