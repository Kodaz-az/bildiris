<?php
/**
 * Get VAPID Public Key API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/vapid-keys.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    echo json_encode([
        'success' => true,
        'publicKey' => VapidKeys::getPublicKey()
    ]);
} catch (Exception $e) {
    error_log("Error in get-vapid-key.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}
?>