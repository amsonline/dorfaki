<?php
/**
 * Divar API Proxy — src/api/divar.php
 *
 * Fetches premium panel data from divar.ir/pro/FFIEsACX via POST request.
 * Parses the server-rendered HTML response to extract embedded JSON state.
 *
 * Usage:
 *   POST /api/divar
 *
 * Requirements: PHP 7.4+ with cURL extension
 */

declare(strict_types=1);

// Ensure JSON response
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'روش درخواست نامعتبر.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$divarUrl = 'https://api.divar.ir/v8/premium-user/web/business/brand-landing/FFIEsACX';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $divarUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER     => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: fa-IR,fa;q=0.9,en;q=0.8',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'Origin: https://divar.ir',
        'Referer: https://divar.ir/',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'خطا در دریافت داده‌ها از دیوار.',
        'debug' => $curlError ?: "HTTP {$httpCode}",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Try to extract window.__PRELOADED_STATE__ from the HTML
$preloadedState = null;
if (preg_match(
    '/window\.__PRELOADED_STATE__\s*=\s*(\{.*?\});<\/script>/s',
    $response,
    $matches
)) {
    $jsonStr = $matches[1];
    $preloadedState = json_decode($jsonStr, true);
}

// Build response from preloaded state if available
if ($preloadedState
    && isset($preloadedState['premiumPanel']['landing'])
) {
    $landing = $preloadedState['premiumPanel']['landing'];

    $result = [
        'title' => $landing['pageTitle'] ?? 'آگهی‌های مشاور اداری و تجاری (درفکی) در دیوار',
        'header_widget_list' => $landing['headerWidgets'] ?? [],
        'post_row_widget_list' => $landing['postRowWidgets'] ?? [],
        'infinite_scroll_response' => $landing['pagination'] ?? [
            'last_item_identifier' => '1',
        ],
    ];

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

http_response_code(200);
echo $response;


// Fallback: return empty structure if no data could be extracted
// http_response_code(200);
// echo json_encode([
//     'title' => 'آگهی‌های مشاور اداری و تجاری (درفکی) در دیوار',
//     'header_widget_list' => [],
//     'post_row_widget_list' => [],
//     'infinite_scroll_response' => [
//         'last_item_identifier' => '1',
//     ],
// ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
