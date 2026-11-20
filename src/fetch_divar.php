<?php
declare(strict_types=1);

/**
 * Dorfaki — Fetch Divar Ads and Store in Database
 *
 * Fetches data from Divar premium panel, parses the embedded JSON state,
 * and stores/updates ads in the database. Ads that are no longer present
 * in the Divar feed are marked as hidden (is_hidden=1) unless the preserve
 * flag is set to true.
 *
 * Usage (via command line):
 *   php src/fetch_divar.php
 *
 * Requirements: PHP 7.4+ with cURL and PDO_MYSQL extensions.
 */

require_once __DIR__ . '/db.php';

const DEFAULT_DIVAR_MERCHANT_ID = 'FFIEsACX';

/**
 * Get the Divar merchant ID from the database, falling back to default.
 *
 * @return string
 */
function getDivarMerchantId(): string
{
    $pdo = dorfakiDb();
    $stmt = $pdo->prepare('SELECT divar_merchant_id FROM website_info WHERE id = 1');
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row && !empty($row['divar_merchant_id'])) {
        return $row['divar_merchant_id'];
    }
    return DEFAULT_DIVAR_MERCHANT_ID;
}

/**
 * Fetch the raw HTML response from Divar's premium panel API.
 *
 * @return string|false HTML response or false on error
 */
function fetchDivarHtml(): ?string
{
    $merchantId = getDivarMerchantId();
    $divarUrl = 'https://api.divar.ir/v8/premium-user/web/business/brand-landing/' . $merchantId;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $divarUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
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
        error_log("Divar fetch error: HTTP {$httpCode} - {$curlError}");
        return false;
    }

    return $response;
}

/**
 * Extract the window.__PRELOADED_STATE__ JSON from the HTML.
 *
 * @param string $html HTML content
 * @return array|false Decoded JSON state or false if not found
 */
function extractPreloadedState(string $html): array|false
{
    if (preg_match(
        '/window\\.__PRELOADED_STATE__\\s*=\\s*(\\{.*?\\});<\\/script>/s',
        $html,
        $matches
    )) {
        $jsonStr = $matches[1];
        $state = json_decode($jsonStr, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $state;
        }
    }

    // die(print_r(json_decode($html, true), true));
    return json_decode($html, true);
    return false;
}

/**
 * Extract ads from the preloaded state's post_row_widget_list.
 *
 * @param array $state The preloaded state JSON
 * @return array Array of ad data arrays
 */
function extractAdsFromState(array $state): array
{
    // die(print_r(array_keys($state), true));
    $ads = [];

    if (!isset($state['premiumPanel']['landing']['postRowWidgets'])) {
        if (!isset($state['post_row_widget_list'])) {
            return $ads;
        }
    }

    if (isset($state['post_row_widget_list'])) {
        $rawAds = $state['post_row_widget_list'];
    }else{
        $rawAds = $state['premiumPanel']['landing']['postRowWidgets'];
    }


    foreach ($rawAds as $widget) {
        // die(print_r($widget, true));
        if (!isset($widget['widget_type']) || $widget['widget_type'] !== 'POST_ROW') {
            continue;
        }

        $data = $widget['data'] ?? [];

        // Extract ad identifier
        $adId = $data['token'] ?? ($data['ad_id'] ?? ($data['widget_id'] ?? null));
        if (empty($adId)) {
            $adId = md5(serialize($data));
        }

        $ad = [
            'divar_ad_id'      => $adId,
            'title'            => $data['title'] ?? '',
            'price'            => $data['price'] ?? ($data['middle_description_text'] ?? ''),
            'location'         => $data['location'] ?? ($data['area'] ?? ($data['bottom_description_text'] ?? '')),
            'image_url'        => $data['image'] ?? ($data['images'][0] ?? ($data['image_url'] ?? '')),
            'description'      => $data['description'] ?? '',
            'url'              => $data['url'] ?? ($data['link'] ?? ''),
            'widget_id'        => $data['widget_id'] ?? '',
            'preserve'         => 0,
            'is_hidden'        => 0,
        ];

        $ads[] = $ad;
    }

    return $ads;
}

/**
 * Save ads to the database: insert new ones, update existing ones,
 * and mark removed ads as hidden (respecting the preserve flag).
 *
 * @param array $newAds Ads data extracted from Divar
 * @return array Summary of operations [inserted, updated, marked_hidden]
 */
function saveAdsToDatabase(array $newAds): array
{
    $pdo = dorfakiDb();

    // Step 1: Get existing ads that have preserve=1
    $stmt = $pdo->prepare("SELECT divar_ad_id, preserve FROM divar_ads WHERE preserve = 1");
    $stmt->execute();
    $preservedAds = [];
    while ($row = $stmt->fetch()) {
        $preservedAds[$row['divar_ad_id']] = true;
    }

    // Step 2: Get all current ad IDs from the new fetch
    $newAdIds = array_column($newAds, 'divar_ad_id');

    // Step 3: Insert or update each ad
    $inserted = 0;
    $updated = 0;

    foreach ($newAds as $ad) {
        $adId = $ad['divar_ad_id'];

        // Check if ad already exists
        $stmt = $pdo->prepare(
            'SELECT id, preserve FROM divar_ads WHERE divar_ad_id = :ad_id LIMIT 1'
        );
        $stmt->execute([':ad_id' => $adId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing ad - preserve the existing preserve flag
            $updateStmt = $pdo->prepare(
                "UPDATE divar_ads SET
                    title = :title,
                    price = :price,
                    location = :location,
                    image_url = :image_url,
                    description = :description,
                    url = :url,
                    widget_id = :widget_id,
                    is_hidden = 0,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE divar_ad_id = :ad_id"
            );

            $updateStmt->execute([
                ':title' => $ad['title'],
                ':price' => $ad['price'],
                ':location' => $ad['location'],
                ':image_url' => $ad['image_url'],
                ':description' => $ad['description'],
                ':url' => $ad['url'],
                ':widget_id' => $ad['widget_id'],
                ':ad_id' => $adId,
            ]);

            $updated++;
        } else {
            // Insert new ad
            $insertStmt = $pdo->prepare(
                "INSERT INTO divar_ads
                    (divar_ad_id, title, price, location, image_url, description, url, widget_id, preserve, is_hidden)
                 VALUES
                    (:ad_id, :title, :price, :location, :image_url, :description, :url, :widget_id, :preserve, :is_hidden)"
            );

            $insertStmt->execute([
                ':ad_id' => $adId,
                ':title' => $ad['title'],
                ':price' => $ad['price'],
                ':location' => $ad['location'],
                ':image_url' => $ad['image_url'],
                ':description' => $ad['description'],
                ':url' => $ad['url'],
                ':widget_id' => $ad['widget_id'],
                ':preserve' => 0,
                ':is_hidden' => 0,
            ]);

            $inserted++;
        }
    }

    // Step 4: Mark ads as hidden that are no longer in the Divar feed
    // but respect the preserve flag - ads with preserve=1 are never hidden
    $markedHidden = 0;

    if (empty($newAdIds)) {
        // No new fetch; mark all non-preserved ads as hidden
        $stmt = $pdo->prepare("SELECT divar_ad_id FROM divar_ads WHERE preserve = 0");
        $stmt->execute();
        $adsToHide = [];
        while ($row = $stmt->fetch()) {
            $adsToHide[] = $row['divar_ad_id'];
        }
    } else {
        // Get all ad IDs currently in the database
        $stmt = $pdo->prepare("SELECT divar_ad_id, preserve FROM divar_ads");
        $stmt->execute();
        $dbAdIds = [];
        $dbPreserved = [];
        while ($row = $stmt->fetch()) {
            $dbAdIds[] = $row['divar_ad_id'];
            if ($row['preserve'] == 1) {
                $dbPreserved[$row['divar_ad_id']] = true;
            }
        }

        // Ads in DB but not in new fetch (and not preserved) should be hidden
        $adsToHide = array_diff($dbAdIds, $newAdIds);

        // Remove preserved ads from the hide list
        $adsToHide = array_values(array_filter($adsToHide, function ($adId) use ($dbPreserved) {
            return !($dbPreserved[$adId] ?? false);
        }));
    }

    // Mark ads as hidden
    if (!empty($adsToHide)) {
        foreach ($adsToHide as $adId) {
            $updateStmt = $pdo->prepare(
                'UPDATE divar_ads SET is_hidden = 1 WHERE divar_ad_id = :ad_id'
            );
            $updateStmt->execute([':ad_id' => $adId]);
            $markedHidden++;
        }
    }

    return [
        'inserted' => $inserted,
        'updated' => $updated,
        'marked_hidden' => $markedHidden,
    ];
}

/**
 * Main execution: fetch Divar data and store in database.
 *
 * @return array Results summary
 */
function runFetchDivar(): array
{
    // Fetch HTML from Divar
    $html = fetchDivarHtml();
    if ($html === false) {
        return [
            'success' => false,
            'message' => 'Could not fetch data from Divar. Check logs for details.',
        ];
    }

    // Extract preloaded state
    $state = extractPreloadedState($html);
    if ($state === false) {
        return [
            'success' => false,
            'message' => 'Could not extract preloaded state from Divar response.',
        ];
    }

    // Extract ads from the state
    $ads = extractAdsFromState($state);
    if (empty($ads)) {
        return [
            'success' => true,
            'message' => 'No ads found in Divar response.',
            'inserted' => 0,
            'updated' => 0,
            'marked_hidden' => 0,
        ];
    }

    // Save to database
    $results = saveAdsToDatabase($ads);

    return [
        'success' => true,
        'message' => 'Divar ads successfully synced.',
        'inserted' => $results['inserted'],
        'updated' => $results['updated'],
        'marked_hidden' => $results['marked_hidden'],
        'total_ads_fetched' => count($ads),
    ];
}

// Run the script if executed directly
if (php_sapi_name() === 'cli') {
    $result = runFetchDivar();

    echo "=== Divar Ads Sync Results ===\n";
    echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    if ($result['success']) {
        echo "Inserted: " . $result['inserted'] . "\n";
        echo "Updated: " . $result['updated'] . "\n";
        echo "Marked as hidden: " . $result['marked_hidden'] . "\n";
    }

    exit($result['success'] ? 0 : 1);
}
