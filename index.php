<?php
declare(strict_types=1);

/**
 * Dorfaki - Real Estate Advisor Website
 * PHP Front Controller (replaces Node.js/Express server.js)
 *
 * Features:
 * - Serves static assets (CSS, JS, fonts, images) from /public
 * - Serves the Persian single-page application at /
 * - API endpoint for contact form at /api/contact
 * - Health check endpoint at /health
 * - File-based rate limiting
 * - Security headers (CSP, X-Content-Type-Options, etc.)
 *
 * Requirements: PHP 7.4+ (no external dependencies)
 *
 * Usage:
 *   Development:  php -S localhost:3000 index.php
 *   Production:   Point your web server document root to this directory
 *                 (Apache: .htaccess included; PHP-FPM/nginx: configure accordingly)
 */

// ===== Configuration =====
define('BASE_DIR', __DIR__);
define('PUBLIC_DIR', BASE_DIR . '/public');
define('VIEWS_DIR', BASE_DIR . '/src/views');
define('STORAGE_DIR', sys_get_temp_dir() . '/dorfaki');
define('RATE_LIMIT_MAX', 100);
define('RATE_LIMIT_WINDOW', 15 * 60); // 15 minutes in seconds

if (!is_dir(STORAGE_DIR)) {
    @mkdir(STORAGE_DIR, 0755, true);
}

$MIME_TYPES = [
    'css'  => 'text/css; charset=utf-8',
    'js'   => 'application/javascript; charset=utf-8',
    'json' => 'application/json; charset=utf-8',
    'html' => 'text/html; charset=utf-8',
    'xml'  => 'application/xml; charset=utf-8',
    'txt'  => 'text/plain; charset=utf-8',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2'=> 'font/woff2',
    'ttf'  => 'font/ttf',
    'otf'  => 'font/otf',
    'eot'  => 'application/vnd.ms-fontobject',
    'pdf'  => 'application/pdf',
];

/**
 * Get the client IP address (handles proxies)
 */
function getClientIp(): string
{
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_CF_CONNECTING_IP',
        'HTTP_TRUE_CLIENT_IP',
    ];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim(explode(',', $_SERVER[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Check rate limiting for the current client IP.
 */
function checkRateLimit(): bool
{
    $ip   = getClientIp();
    $file = STORAGE_DIR . '/ratelimit_' . md5($ip) . '.json';
    $now  = time();

    $data = ['count' => 0, 'reset_time' => $now + RATE_LIMIT_WINDOW];

    if (is_file($file)) {
        $json = @file_get_contents($file);
        if ($json !== false) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && isset($decoded['count'], $decoded['reset_time'])) {
                $data = $decoded;
            }
        }
    }

    if ($now > $data['reset_time']) {
        $data = ['count' => 0, 'reset_time' => $now + RATE_LIMIT_WINDOW];
    }

    $data['count']++;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    return $data['count'] <= RATE_LIMIT_MAX;
}

/**
 * Send a JSON response and exit.
 */
function sendJson(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Serve a static file from the public/ directory.
 */
function serveStaticFile(string $uri, array $mimeTypes): void
{
    $relativePath = ltrim($uri, '/');

    if (strpos($relativePath, '..') !== false || strpos($relativePath, "\0") !== false) {
        http_response_code(403);
        exit;
    }

    $filePath   = PUBLIC_DIR . '/' . $relativePath;
    $realPath   = realpath($filePath);
    $realPublic = realpath(PUBLIC_DIR);

    if ($realPath === false || !is_file($realPath) || !is_readable($realPath)) {
        http_response_code(404);
        exit;
    }

    if (strpos($realPath, $realPublic . DIRECTORY_SEPARATOR) !== 0) {
        http_response_code(403);
        exit;
    }

    $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $mimeType  = $mimeTypes[$extension] ?? 'application/octet-stream';

    $modifiedTime = filemtime($realPath);
    $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
    if (!empty($ifModifiedSince)) {
        $since = strtotime($ifModifiedSince);
        if ($since !== false && $since >= $modifiedTime) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($realPath));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modifiedTime) . ' GMT');
    header('Cache-Control: public, max-age=3600');

    readfile($realPath);
    exit;
}

/**
 * END OF HELPER FUNCTIONS
 */

// ===== Security Headers =====
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' code.jquery.com cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com data:; connect-src 'self';");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// ===== Request Parsing =====
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri    = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

// ===== Static File Serving =====
// Serve static files from public/ — skip rate limiting for efficiency.
if ($uri !== '/' && is_file(PUBLIC_DIR . $uri) && is_readable(PUBLIC_DIR . $uri)) {
    serveStaticFile($uri, $MIME_TYPES);
}

// ===== Rate Limiting (dynamic routes only) =====
if (!checkRateLimit()) {
    sendJson(429, ['success' => false, 'message' => 'تعداد درخواست بیش از حد. لطفاً صبر کنید.']);
}

// ===== Route Handling =====

// Main page — Single Page Application
if ($method === 'GET' && $uri === '/') {
    $htmlFile = VIEWS_DIR . '/index.html';
    if (is_file($htmlFile) && is_readable($htmlFile)) {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Length: ' . filesize($htmlFile));
        readfile($htmlFile);
        exit;
    }
    sendJson(500, ['success' => false, 'message' => 'خطا در بارگذاری صفحهٔ اصلی.']);
}

// Health check endpoint
if ($method === 'GET' && $uri === '/health') {
    sendJson(200, [
        'status'    => 'ok',
        'service'   => 'dorfaki',
        'timestamp' => date('c'),
    ]);
}

// Contact form API endpoint
if ($method === 'POST' && $uri === '/api/contact') {
    // Accept both JSON and form-encoded input
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $rawInput = file_get_contents('php://input');
        $data     = json_decode($rawInput, true) ?: [];
    } else {
        $data = $_POST ?: [];
    }

    $name    = trim($data['name'] ?? '');
    $phone   = trim($data['phone'] ?? '');
    $email   = trim($data['email'] ?? '');
    $message = trim($data['message'] ?? '');

    // Basic validation — name, phone, and message are required
    if (empty($name) || empty($phone) || empty($message)) {
        sendJson(400, ['success' => false, 'message' => 'لطفاً تمام فیلدهای ضروری را پر کنید.']);
    }

    // Validate email format if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJson(400, ['success' => false, 'message' => 'فرمت ایمیل نامعتبر است.']);
    }

    // In a real application, this would send an email or save to a database.
    error_log('تماس جدید دریافت شد: ' . json_encode([
        'name'    => $name,
        'phone'   => $phone,
        'email'   => $email,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE));

    sendJson(200, [
        'success' => true,
        'message' => 'پیام شما با موفقیت ارسال شد!',
    ]);
}

// Divar listings proxy endpoint — delegates to separate API file
if ($method === 'POST' && $uri === '/api/divar') {
    require_once BASE_DIR . '/src/api/divar.php';
    exit;
}

// If the URI matches a known route but the method doesn't → 405
if ($uri === '/health' || $uri === '/api/contact' || $uri === '/api/divar') {
    sendJson(405, ['success' => false, 'message' => 'روش درخواست نامعتبر.']);
}

// ===== 404 Handler =====
sendJson(404, ['success' => false, 'message' => 'صفحه مورد نظر یافت نشد.']);
