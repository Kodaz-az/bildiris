<?php
/**
 * Admin Dashboard
 */

require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bildiris</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="admin-header">
            <div>
                <h1>📊 Admin Dashboard</h1>
                <p>Xoş gəlmisiniz, <?php echo htmlspecialchars($admin_username); ?>!</p>
            </div>
            <div>
                <button id="refreshBtn" class="btn btn-info">🔄 Yenilə</button>
                <button id="logoutBtn" class="btn btn-danger">Çıxış</button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number" id="totalUsers">-</div>
                <div class="stat-label">Ümumi İstifadəçi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="subscribedUsers">-</div>
                <div class="stat-label">Abunə Olan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="unsubscribedUsers">-</div>
                <div class="stat-label">Abunə Olmayan</div>
            </div>
        </div>

        <!-- Send Notification Form -->
        <div class="card">
            <h2>📢 Bildiriş Göndər</h2>
            <form id="sendNotificationForm">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="type">Bildiriş Növü:</label>
                        <select id="type" name="type" required>
                            <option value="">Seçin</option>
                            <option value="single">Fərdi İstifadəçi</option>
                            <option value="broadcast">Bütün Abunəçilər</option>
                        </select>
                    </div>
                    <div class="form-group" id="recipientGroup" style="display: none;">
                        <label for="recipient">İstifadəçi Email:</label>
                        <input type="email" id="recipient" name="recipient" placeholder="user@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="title">Başlıq:</label>
                    <input type="text" id="title" name="title" required placeholder="Bildiriş başlığı">
                </div>
                <div class="form-group">
                    <label for="message">Mesaj:</label>
                    <textarea id="message" name="message" required placeholder="Bildiriş mətni..." rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success">📤 Bildiriş Göndər</button>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <h2>👥 Qeydiyyatlı İstifadəçilər</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Email</th>
                            <th>Abunəlik</th>
                            <th>Qeydiyyat Tarixi</th>
                            <th>Əməliyyat</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="6" style="text-align: center;">Məlumatlar yüklənir...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <footer>
            <p>&copy; 2024 Bildiris. Bütün hüquqlar qorunur.</p>
            <p><a href="../index.php">Ana Səhifə</a></p>
        </footer>
    </div>

    <!-- Status notifications -->
    <div id="notification" class="notification"></div>

    <script src="../js/main.js"></script>
    <script>
        // Show/hide recipient field based on notification type
        document.getElementById('type').addEventListener('change', function() {
            const recipientGroup = document.getElementById('recipientGroup');
            const recipientField = document.getElementById('recipient');
            
            if (this.value === 'single') {
                recipientGroup.style.display = 'block';
                recipientField.required = true;
            } else {
                recipientGroup.style.display = 'none';
                recipientField.required = false;
                recipientField.value = '';
            }
        });
    </script>
</body>
</html>