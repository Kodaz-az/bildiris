<?php
/**
 * Admin Login Page
 */

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'İstifadəçi adı və ya şifrə yalnışdır';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Giriş zamanı xəta baş verdi';
        }
    } else {
        $error = 'İstifadəçi adı və şifrə tələb olunur';
    }
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - Bildiris</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Admin Paneli</h1>
            <p>Bildiris İdarə Sistemi</p>
        </header>

        <main>
            <div class="card" style="max-width: 400px; margin: 0 auto;">
                <h2>Admin Giriş</h2>
                
                <?php if ($error): ?>
                    <div class="status-message status-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">İstifadəçi adı:</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Şifrə:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Giriş</button>
                </form>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 0.9rem; color: #666;">
                    <strong>Standart Admin Hesabı:</strong><br>
                    İstifadəçi adı: admin<br>
                    Şifrə: admin123
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Bildiris. Bütün hüquqlar qorunur.</p>
            <p><a href="../index.php">Ana Səhifə</a></p>
        </footer>
    </div>
</body>
</html>