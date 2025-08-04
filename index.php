<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildiris - Push Bildiriş Sistemi</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔔 Bildiris</h1>
            <p>Real Push Bildiriş Sistemi</p>
        </header>

        <main>
            <div class="card">
                <h2>İstifadəçi Qeydiyyatı</h2>
                <form id="registrationForm">
                    <div class="form-group">
                        <label for="name">Ad və Soyad:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Qeydiyyatdan Keç</button>
                </form>
            </div>

            <div class="card" id="subscriptionCard" style="display: none;">
                <h2>Push Bildirişlər</h2>
                <p>Bildirişləri qəbul etmək üçün icazə verin</p>
                <button id="subscribeBtn" class="btn btn-success">Bildirişlərə Abunə Ol</button>
                <div id="subscriptionStatus" class="status-message"></div>
            </div>

            <div class="card">
                <h2>Test Bildirişi</h2>
                <p>Sistem test etmək üçün özünüzə bildiriş göndərin</p>
                <button id="testNotificationBtn" class="btn btn-info" disabled>Test Bildirişi Göndər</button>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Bildiris. Bütün hüquqlar qorunur.</p>
            <p><a href="admin/login.php">Admin Panel</a></p>
        </footer>
    </div>

    <!-- Status notifications -->
    <div id="notification" class="notification"></div>

    <script src="js/main.js"></script>
</body>
</html>