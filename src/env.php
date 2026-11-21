<?php
declare(strict_types=1);

/**
 * Dorfaki — Environment Variables Loader
 * Loads .env file and sets $_ENV and $_SERVER variables
 * 
 * Usage: require_once __DIR__ . '/../env.php';
 * Or: require_once __DIR__ . '/env.php';
 */

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#') || str_starts_with($line, ';')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, "\"'");
            if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}