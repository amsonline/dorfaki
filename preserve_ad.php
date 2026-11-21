<?php
declare(strict_types=1);

require_once __DIR__ . '/src/db.php';

// Get values - be very careful
$adId = $_POST['ad_id'] ?? '0';
$preserve = isset($_POST['preserve']) && $_POST['preserve'] == '1' ? 1 : 0;

// Toggle
$newPreserve = !$preserve;

if (!empty($adId)) {
    $pdo = dorfakiDb();
    $stmt = $pdo->prepare('UPDATE divar_ads SET preserve = :p WHERE divar_ad_id = :a');
    $stmt->execute([':p' => ($newPreserve === true ? 1 : 0), ':a' => $adId]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'preserve' => $newPreserve]);
}
