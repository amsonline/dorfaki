<?php
/** 
 * Dorfaki Divar Ads API — src/api/divar.php
 *
 * Serves Divar premium panel ads data from the database instead of
 * contacting Divar directly on every request. This reduces load on
 * Divar's API and improves page load times.
 *
 * Data is populated by running: php src/fetch_divar.php
 *
 * Usage:
 *   POST /api/divar
 *
 * Requirements: PHP 7.4+ with PDO extension
 */
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

// Ensure JSON response
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'POST';

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'روش درخواست نامعتبر.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fetch ads from database instead of contacting Divar directly
try {
    $pdo = dorfakiDb();

    // Get non-hidden ads, order by most recently updated
    $stmt = $pdo->prepare("
        SELECT divar_ad_id, title, price, location, image_url, description, url
        FROM divar_ads
        WHERE is_hidden = 0
        ORDER BY updated_at DESC
    ");
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no non-hidden ads, get all ads (including preserved ones that shouldn't be hidden)
    if (empty($ads)) {
        $stmt = $pdo->prepare("
            SELECT divar_ad_id, title, price, location, image_url, description, url
            FROM divar_ads
            ORDER BY updated_at DESC
        ");
        $stmt->execute();
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Transform database ads to the format expected by the frontend
    // Frontend expects: post_row_widget_list with widgets having widget_type and data
    $postRowWidgets = [];
    foreach ($ads as $ad) {
        $postRowWidgets[] = [
            'widget_type' => 'POST_ROW',
            'data' => [
                'title' => $ad['title'] ?? '',
                'image_url' => $ad['image_url'] ?? '',
                'middle_description_text' => $ad['price'] ?? '',
                'bottom_description_text' => $ad['location'] ?? '',
                'top_description_text' => $ad['description'] ?? '',
                'red_text' => '',
                'token' => $ad['divar_ad_id'] ?? '',
            ],
        ];
    }

    // Build the response structure matching the original Divar API format
    $result = [
        'title' => 'آگهی‌های مشاور اداری و تجاری (درفکی) در دیوار',
        'header_widget_list' => [],
        'post_row_widget_list' => $postRowWidgets,
        'infinite_scroll_response' => [
            'last_item_identifier' => '1',
        ],
    ];

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'خطا در بارگذاری داده‌های آگهی.',
        'debug' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}