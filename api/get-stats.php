<?php
/**
 * Get Statistics API - Admin only
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Check if admin session exists
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Database connection
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Get statistics
    $stats = [];

    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch()['count'];

    // Subscribed users
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE is_subscribed = 1");
    $stmt->execute();
    $stats['subscribed_users'] = $stmt->fetch()['count'];

    // Unsubscribed users
    $stats['unsubscribed_users'] = $stats['total_users'] - $stats['subscribed_users'];

    // Users registered today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['users_today'] = $stmt->fetch()['count'];

    // Users registered this week
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $stats['users_this_week'] = $stmt->fetch()['count'];

    // Users registered this month
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $stats['users_this_month'] = $stmt->fetch()['count'];

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Database error in get-stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database xətası baş verdi']);
} catch (Exception $e) {
    error_log("General error in get-stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}
?>