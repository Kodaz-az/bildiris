<?php
/**
 * Database Installation Script
 * Run this file once to create the necessary database and tables
 */

require_once 'config/database.php';

try {
    // First, create the database
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $db_name = 'bildiris_db';
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8 COLLATE utf8_unicode_ci";
    $pdo->exec($sql);
    echo "Database '$db_name' created successfully or already exists.<br>";
    
    // Connect to the new database
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        subscription_endpoint TEXT,
        subscription_p256dh VARCHAR(255),
        subscription_auth VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_subscribed BOOLEAN DEFAULT FALSE,
        INDEX idx_email (email),
        INDEX idx_subscribed (is_subscribed)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    
    $pdo->exec($sql);
    echo "Table 'users' created successfully.<br>";
    
    // Create admin table
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    
    $pdo->exec($sql);
    echo "Table 'admin' created successfully.<br>";
    
    // Insert default admin user (username: admin, password: admin123)
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $sql = "INSERT IGNORE INTO admin (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_username, $admin_password]);
    
    if ($stmt->rowCount() > 0) {
        echo "Default admin user created successfully.<br>";
        echo "<strong>Admin Login:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    
    echo "<br><h3>Installation completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to User Registration Page</a></p>";
    echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
    
} catch(PDOException $e) {
    echo "Installation error: " . $e->getMessage();
}
?>