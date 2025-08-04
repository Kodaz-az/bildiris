/**
 * Main JavaScript for Bildiris Push Notification System
 */

class BildirisApp {
    constructor() {
        this.currentUser = null;
        this.isSubscribed = false;
        this.init();
    }

    async init() {
        // Check for service worker support
        if ('serviceWorker' in navigator) {
            try {
                await navigator.serviceWorker.register('js/sw.js');
                console.log('Service Worker registered successfully');
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }

        // Check for push notification support
        if (!('PushManager' in window)) {
            this.showNotification('Bu brauzer push bildirişləri dəstəkləmir', 'error');
            return;
        }

        this.bindEvents();
        this.checkPermission();
    }

    bindEvents() {
        // Registration form
        const registrationForm = document.getElementById('registrationForm');
        if (registrationForm) {
            registrationForm.addEventListener('submit', (e) => this.handleRegistration(e));
        }

        // Subscribe button
        const subscribeBtn = document.getElementById('subscribeBtn');
        if (subscribeBtn) {
            subscribeBtn.addEventListener('click', () => this.handleSubscription());
        }

        // Test notification button
        const testBtn = document.getElementById('testNotificationBtn');
        if (testBtn) {
            testBtn.addEventListener('click', () => this.sendTestNotification());
        }
    }

    async handleRegistration(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const userData = {
            name: formData.get('name'),
            email: formData.get('email')
        };

        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (result.success) {
                this.currentUser = result.user;
                this.showNotification('Qeydiyyat uğurlu oldu!', 'success');
                
                // Show subscription card
                document.getElementById('subscriptionCard').style.display = 'block';
                document.getElementById('subscriptionCard').classList.add('fade-in');
                
                // Enable test button if user is subscribed
                if (result.user.is_subscribed) {
                    document.getElementById('testNotificationBtn').disabled = false;
                }
            } else {
                this.showNotification(result.message || 'Qeydiyyat zamanı xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showNotification('Şəbəkə xətası baş verdi', 'error');
        }
    }

    async handleSubscription() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                this.showNotification('Bildiriş icazəsi verilmədi', 'error');
                return;
            }

            const registration = await navigator.serviceWorker.ready;
            
            // Get VAPID public key
            const vapidResponse = await fetch('api/get-vapid-key.php');
            const vapidData = await vapidResponse.json();
            
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(vapidData.publicKey)
            });

            // Send subscription to server
            const response = await fetch('api/subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: this.currentUser?.email,
                    subscription: subscription
                })
            });

            const result = await response.json();

            if (result.success) {
                this.isSubscribed = true;
                this.showNotification('Push bildirişlərə uğurla abunə oldunuz!', 'success');
                
                // Update subscription status
                const statusDiv = document.getElementById('subscriptionStatus');
                statusDiv.innerHTML = '<div class="status-message status-success">✅ Push bildirişlərə abunəsiniz</div>';
                
                // Enable test button
                document.getElementById('testNotificationBtn').disabled = false;
                
                // Hide subscribe button
                document.getElementById('subscribeBtn').style.display = 'none';
            } else {
                this.showNotification(result.message || 'Abunəlik zamanı xəta baş verdi', 'error');
            }

        } catch (error) {
            console.error('Subscription error:', error);
            this.showNotification('Abunəlik zamanı xəta baş verdi', 'error');
        }
    }

    async sendTestNotification() {
        if (!this.currentUser) {
            this.showNotification('Əvvəlcə qeydiyyatdan keçin', 'error');
            return;
        }

        try {
            const response = await fetch('api/send-push.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'test',
                    email: this.currentUser.email,
                    title: 'Test Bildirişi',
                    message: 'Bu bir test bildirişidir. Sistem düzgün işləyir!'
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Test bildirişi göndərildi!', 'success');
            } else {
                this.showNotification(result.message || 'Bildiriş göndərilərkən xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Test notification error:', error);
            this.showNotification('Test bildirişi göndərilərkən xəta baş verdi', 'error');
        }
    }

    checkPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                // Permission already granted
                console.log('Notification permission already granted');
            } else if (Notification.permission !== 'denied') {
                // Permission not decided yet
                console.log('Notification permission not decided');
            } else {
                // Permission denied
                console.log('Notification permission denied');
            }
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.classList.add('show');

        setTimeout(() => {
            notification.classList.remove('show');
        }, 4000);
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BildirisApp();
});

// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        if (window.location.pathname.includes('admin')) {
            this.init();
        }
    }

    async init() {
        this.bindAdminEvents();
        await this.loadUsers();
        await this.loadStats();
    }

    bindAdminEvents() {
        // Send notification form
        const sendForm = document.getElementById('sendNotificationForm');
        if (sendForm) {
            sendForm.addEventListener('submit', (e) => this.handleSendNotification(e));
        }

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }

        // Refresh data button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadUsers();
                this.loadStats();
            });
        }
    }

    async loadUsers() {
        try {
            const response = await fetch('api/get-users.php');
            const result = await response.json();

            if (result.success) {
                this.displayUsers(result.users);
            } else {
                console.error('Failed to load users:', result.message);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    async loadStats() {
        try {
            const response = await fetch('api/get-stats.php');
            const result = await response.json();

            if (result.success) {
                this.displayStats(result.stats);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    displayUsers(users) {
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.is_subscribed ? '✅ Bəli' : '❌ Xeyr'}</td>
                <td>${new Date(user.created_at).toLocaleDateString('az-AZ')}</td>
                <td>
                    ${user.is_subscribed ? 
                        `<button class="btn btn-info btn-sm" onclick="adminPanel.sendToUser('${user.email}')">Bildiriş Göndər</button>` : 
                        '<span class="text-muted">Abunə deyil</span>'
                    }
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    displayStats(stats) {
        const totalUsers = document.getElementById('totalUsers');
        const subscribedUsers = document.getElementById('subscribedUsers');
        const unsubscribedUsers = document.getElementById('unsubscribedUsers');

        if (totalUsers) totalUsers.textContent = stats.total_users || 0;
        if (subscribedUsers) subscribedUsers.textContent = stats.subscribed_users || 0;
        if (unsubscribedUsers) unsubscribedUsers.textContent = stats.unsubscribed_users || 0;
    }

    async handleSendNotification(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const notificationData = {
            type: formData.get('type'),
            recipient: formData.get('recipient'),
            title: formData.get('title'),
            message: formData.get('message')
        };

        try {
            const response = await fetch('api/send-push.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(notificationData)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Bildiriş uğurla göndərildi!', 'success');
                e.target.reset();
            } else {
                this.showNotification(result.message || 'Bildiriş göndərilərkən xəta baş verdi', 'error');
            }
        } catch (error) {
            console.error('Send notification error:', error);
            this.showNotification('Bildiriş göndərilərkən xəta baş verdi', 'error');
        }
    }

    sendToUser(email) {
        const recipientField = document.getElementById('recipient');
        const typeField = document.getElementById('type');
        
        if (recipientField && typeField) {
            recipientField.value = email;
            typeField.value = 'single';
            
            // Scroll to form
            document.getElementById('sendNotificationForm').scrollIntoView({
                behavior: 'smooth'
            });
        }
    }

    logout() {
        if (confirm('Çıxış etmək istədiyinizə əminsiniz?')) {
            window.location.href = 'logout.php';
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.classList.add('show');

        setTimeout(() => {
            notification.classList.remove('show');
        }, 4000);
    }
}

// Initialize admin panel if on admin pages
const adminPanel = new AdminPanel();