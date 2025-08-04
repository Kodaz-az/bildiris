<?php
/**
 * Get Users API - Admin only
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

    // Get all users with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;

    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $totalCount = $stmt->fetch()['total'];

    // Get users with pagination
    $stmt = $pdo->prepare("
        SELECT id, name, email, is_subscribed, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'totalPages' => ceil($totalCount / $limit)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in get-users.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database xətası baş verdi']);
} catch (Exception $e) {
    error_log("General error in get-users.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}
?>