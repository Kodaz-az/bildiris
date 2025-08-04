<?php
/**
 * Send Push Notification API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/vapid-keys.php';

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
    $type = $input['type'] ?? '';
    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');
    $recipient = trim($input['recipient'] ?? '');
    $email = trim($input['email'] ?? '');

    if (empty($title) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Başlıq və mesaj tələb olunur']);
        exit;
    }

    // Sanitize input
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Database connection
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $recipients = [];

    // Determine recipients based on type
    if ($type === 'test' && !empty($email)) {
        // Test notification to specific user
        $stmt = $pdo->prepare("
            SELECT email, subscription_endpoint, subscription_p256dh, subscription_auth 
            FROM users 
            WHERE email = ? AND is_subscribed = 1
        ");
        $stmt->execute([$email]);
        $recipients = $stmt->fetchAll();
    } elseif ($type === 'single' && !empty($recipient)) {
        // Single user notification
        $stmt = $pdo->prepare("
            SELECT email, subscription_endpoint, subscription_p256dh, subscription_auth 
            FROM users 
            WHERE email = ? AND is_subscribed = 1
        ");
        $stmt->execute([$recipient]);
        $recipients = $stmt->fetchAll();
    } elseif ($type === 'broadcast') {
        // Broadcast to all subscribed users
        $stmt = $pdo->prepare("
            SELECT email, subscription_endpoint, subscription_p256dh, subscription_auth 
            FROM users 
            WHERE is_subscribed = 1
        ");
        $stmt->execute();
        $recipients = $stmt->fetchAll();
    } else {
        echo json_encode(['success' => false, 'message' => 'Düzgün bildiriş növü seçin']);
        exit;
    }

    if (empty($recipients)) {
        echo json_encode(['success' => false, 'message' => 'Bildiriş göndəriləcək abunəçi tapılmadı']);
        exit;
    }

    // Prepare notification payload
    $payload = json_encode([
        'title' => $title,
        'body' => $message,
        'icon' => '/bildiris/images/icon-192x192.png',
        'badge' => '/bildiris/images/badge-72x72.png',
        'data' => [
            'url' => '/bildiris/',
            'timestamp' => time()
        ]
    ]);

    $successCount = 0;
    $failedCount = 0;

    // Send notifications to all recipients
    foreach ($recipients as $recipient) {
        $result = sendPushNotification(
            $recipient['subscription_endpoint'],
            $recipient['subscription_p256dh'],
            $recipient['subscription_auth'],
            $payload
        );

        if ($result) {
            $successCount++;
        } else {
            $failedCount++;
            // Log failed notification
            error_log("Failed to send push notification to: " . $recipient['email']);
        }
    }

    if ($successCount > 0) {
        $responseMessage = $successCount . " bildiriş uğurla göndərildi";
        if ($failedCount > 0) {
            $responseMessage .= ", " . $failedCount . " bildiriş göndərilə bilmədi";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $responseMessage,
            'sent' => $successCount,
            'failed' => $failedCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Heç bir bildiriş göndərilə bilmədi']);
    }

} catch (Exception $e) {
    error_log("Error in send-push.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}

/**
 * Send push notification using cURL
 */
function sendPushNotification($endpoint, $p256dh, $auth, $payload) {
    try {
        // For this implementation, we'll use a simplified approach
        // In production, you would use a proper Web Push library like web-push-php
        
        // Extract FCM endpoint (if it's a Firebase endpoint)
        if (strpos($endpoint, 'fcm.googleapis.com') !== false) {
            // This is a Firebase endpoint
            $url = $endpoint;
            
            $headers = [
                'Authorization: key=' . getFirebaseServerKey(),
                'Content-Type: application/json'
            ];
            
            // For FCM, we need to modify the payload structure
            $fcmPayload = json_encode([
                'notification' => json_decode($payload, true),
                'to' => extractRegistrationToken($endpoint)
            ]);
            
            return sendHttpRequest($url, $fcmPayload, $headers);
        } else {
            // Generic Web Push endpoint
            // This is a simplified implementation
            // In production, use proper VAPID authentication
            $headers = [
                'Content-Type: application/json',
                'TTL: 86400'
            ];
            
            return sendHttpRequest($endpoint, $payload, $headers);
        }
    } catch (Exception $e) {
        error_log("Push notification error: " . $e->getMessage());
        return false;
    }
}

function sendHttpRequest($url, $data, $headers) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

function getFirebaseServerKey() {
    // Return your Firebase Server Key
    // This should be stored securely, not hardcoded
    return 'YOUR_FIREBASE_SERVER_KEY';
}

function extractRegistrationToken($endpoint) {
    // Extract registration token from FCM endpoint
    $parts = explode('/', $endpoint);
    return end($parts);
}
?>