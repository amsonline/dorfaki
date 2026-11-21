<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

// Get ad ID and preserve status from POST
$adId = $_POST['ad_id'] ?? '';
$preserve = isset($_POST['preserve']) ? 1 : 0;

if (!empty($adId)) {
    $pdo = dorfakiDb();
    var_dump($preserve);
    die();
    $updateStmt = $pdo->prepare(
        'UPDATE divar_ads SET preserve = :preserve WHERE divar_ad_id = :ad_id'
    );
    $updateStmt->execute([':preserve' => $preserve, ':ad_id' => $adId]);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}
