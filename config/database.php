<?php
declare(strict_types=1);

/**
 * Dorfaki — Database Configuration
 *
 * Configuration is read from environment variables (set in .env or your
 * server environment) with sensible defaults below.
 *
 * Example .env:
 *   DB_HOST=127.0.0.1
 *   DB_PORT=3306
 *   DB_DATABASE=dorfaki
 *   DB_USERNAME=dorfaki
 *   DB_PASSWORD=dorfaki123
 */

return [
    'driver'   => 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => (int) ($_ENV['DB_PORT'] ?? 3306),
    'database' => $_ENV['DB_DATABASE'] ?? 'dorfaki',
    'username' => $_ENV['DB_USERNAME'] ?? 'dorfaki',
    'password' => $_ENV['DB_PASSWORD'] ?? 'dorfaki123',
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
];
