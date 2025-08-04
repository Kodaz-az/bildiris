<?php
/**
 * User Registration API
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
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Ad və email tələb olunur']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Düzgün email daxil edin']);
        exit;
    }

    // Sanitize input
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    // Database connection
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id, name, email, is_subscribed, created_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        // User already exists, return existing user data
        echo json_encode([
            'success' => true,
            'message' => 'İstifadəçi artıq mövcuddur',
            'user' => $existingUser
        ]);
        exit;
    }

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, created_at) 
        VALUES (?, ?, NOW())
    ");
    
    $result = $stmt->execute([$name, $email]);

    if ($result) {
        // Get the newly created user
        $userId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT id, name, email, is_subscribed, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newUser = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'İstifadəçi uğurla qeydiyyatdan keçdi',
            'user' => $newUser
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Qeydiyyat zamanı xəta baş verdi']);
    }

} catch (PDOException $e) {
    error_log("Database error in register.php: " . $e->getMessage());
    
    if ($e->getCode() == 23000) { // Duplicate entry
        echo json_encode(['success' => false, 'message' => 'Bu email artıq istifadə edilir']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database xətası baş verdi']);
    }
} catch (Exception $e) {
    error_log("General error in register.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Xəta baş verdi']);
}
?>