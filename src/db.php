<?php
declare(strict_types=1);
require_once __DIR__ . '/env.php';

/**
 * Dorfaki — Database Connection (PDO)
 *
 * Provides a single PDO instance for the application.
 * Requires config/database.php to be loadable.
 */

if (!function_exists('dorfakiDbConfig')) {
    function dorfakiDbConfig(): array
    {
        $configFile = __DIR__ . '/../config/database.php';
        if (is_file($configFile)) {
            $config = require $configFile;
            if (is_array($config)) {
                return $config;
            }
        }
        return [];
    }
}

if (!function_exists('dorfakiDb')) {
    /**
     * Get a PDO connection to the MySQL database.
     *
     * @return PDO
     * @throws PDOException
     */
    function dorfakiDb(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $cfg = dorfakiDbConfig();

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'] ?? '127.0.0.1',
                $cfg['port'] ?? 3306,
                $cfg['database'] ?? 'dorfaki',
                $cfg['charset'] ?? 'utf8mb4'
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $pdo = new PDO(
                $dsn,
                $cfg['username'] ?? '',
                $cfg['password'] ?? '',
                $options
            );
        }

        return $pdo;
    }
}
