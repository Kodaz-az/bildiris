<?php
/**
 * Push Subscription API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Validate input
    $email = trim($input['email'] ?? '');
    $subscription = $input['subscription'] ?? null;

    if (empty($email) || !$subscription) {
        echo json_encode(['success' => false, 'message' => 'Email və subscription məlumatları tələb olunur']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Düzgün email daxil edin']);
        exit;
    }

    // Extract subscription data
    $endpoint = $subscription['endpoint'] ?? '';
    $p256dh = $subscription['keys']['p256dh'] ?? '';
    $auth = $subscription['keys']['auth'] ?? '';

    if (empty($endpoint) || empty($p256dh) || empty($auth)) {
        echo json_encode(['success' => false, 'message' => 'Subscription məlumatları natamam']);
        exit;
    }

    // Sanitize input
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $endpoint = htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8');
    $p256dh = htmlspecialchars($p256dh, ENT_QUOTES, 'UTF-8');
    $auth = htmlspecialchars($auth, ENT_QUOTES, 'UTF-8');

    // Database connection
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'İstifadəçi tapılmadı']);
        exit;
    }

    // Update user subscription
    $stmt = $pdo->prepare("
        UPDATE users 
        SET subscription_endpoint = ?, 
            subscription_p256dh = ?, 
            subscription_auth = ?, 
            is_subscribed = 1 
        WHERE email = ?
    ");
    
    $result = $stmt->execute([$endpoint, $p256dh, $auth, $email]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Push bildirişlərə uğurla abunə oldunuz'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Abunəlik zamanı xəta baş verdi']);
    }

} catch (PDOException $e) {
    error_log("Database error in subscribe.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database xətası baş verdi']);
} catch (Exception $e) {
    error_log("General error in subscribe.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}
?>