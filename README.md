# Bildiris - Real Push Notification System

Bu layihə real web hostingdə işləmək üçün tam funksional push notification sistemidir.

## 🎯 Xüsusiyyətlər

- **İstifadəçi Qeydiyyatı**: Ad və email ilə sadə qeydiyyat
- **Push Notification Abunəliği**: Browser push bildirişlərinə abunə olma
- **Admin Panel**: Qeydiyyatlı istifadəçiləri idarə etmə və bildiriş göndərmə
- **Real-time Bildirişlər**: VAPID keys ilə təhlükəsiz push bildirişlər
- **Responsive Dizayn**: Bütün cihazlarda işləyən modern UI
- **Təhlükəsiz**: SQL injection, XSS və CSRF qorunması

## 📁 Layihə Strukturu

```
bildiris/
├── index.php                 # Ana səhifə (istifadəçi qeydiyyatı)
├── install.php               # Database qurulum scripti
├── manifest.json             # PWA manifest
├── .htaccess                 # Apache konfigurasionu
├── admin/
│   ├── login.php            # Admin girişi
│   ├── dashboard.php        # Admin paneli
│   └── logout.php           # Admin çıxışı
├── api/
│   ├── register.php         # İstifadəçi qeydiyyatı API
│   ├── subscribe.php        # Push subscription API
│   ├── send-push.php        # Push bildiriş göndərmə API
│   ├── get-users.php        # İstifadəçi siyahısı API
│   ├── get-stats.php        # Statistik API
│   └── get-vapid-key.php    # VAPID key API
├── js/
│   ├── main.js              # Ana JavaScript
│   └── sw.js                # Service Worker
├── css/
│   └── style.css            # CSS stillər
└── config/
    ├── database.php         # Database konfigurasionu
    └── vapid-keys.php       # VAPID keys
```

## 🚀 Qurulum

### 1. Faylları Upload Edin
Bütün faylları web hostinginizə upload edin.

### 2. Database Konfiqurasiyası
`config/database.php` faylında database məlumatlarınızı yeniləyin:

```php
private $host = 'localhost';
private $db_name = 'bildiris_db';
private $username = 'your_db_username';
private $password = 'your_db_password';
```

### 3. Database Qurulumu
Brauzerinizdə `install.php` səhifəsini açın və qurulumu başa çatdırın.

### 4. VAPID Keys (İstəyə bağlı)
Production mühitində `config/vapid-keys.php` faylında öz VAPID keys-lərinizi istifadə edin.

## 📱 İstifadə

### İstifadəçi Tərəfi
1. Ana səhifədə ad və email daxil edərək qeydiyyatdan keçin
2. Push bildirişlərə abunə olmaq üçün icazə verin
3. Test bildirişi göndərərək sistemi yoxlayın

### Admin Panel
1. `/admin/login.php` səhifəsinə gedin
2. Standart hesab:
   - İstifadəçi adı: `admin`
   - Şifrə: `admin123`
3. Dashboard-da istifadəçiləri görün və bildiriş göndərin

## 🔧 Konfigurə Etmə

### VAPID Keys Dəyişdirmə
```php
// config/vapid-keys.php
public static $publicKey = 'your_public_key';
public static $privateKey = 'your_private_key';
```

### Database Şifrəsi Dəyişdirmə
```php
$admin_password = password_hash('new_password', PASSWORD_DEFAULT);
```

## 🛡️ Təhlükəsizlik

- SQL injection qorunması
- XSS qorunması  
- CSRF qorunması
- Password hashing
- Session management
- Input validation və sanitization

## 🌐 Browser Dəstəyi

- Chrome 50+
- Firefox 44+
- Safari 16+
- Edge 17+

## 📊 API Endpoints

- `POST /api/register.php` - İstifadəçi qeydiyyatı
- `POST /api/subscribe.php` - Push subscription
- `POST /api/send-push.php` - Push bildiriş göndərmə
- `GET /api/get-users.php` - İstifadəçi siyahısı (Admin only)
- `GET /api/get-stats.php` - Statistikalar (Admin only)
- `GET /api/get-vapid-key.php` - VAPID public key

## 🔍 Test Etmə

1. İstifadəçi qeydiyyatını test edin
2. Push notification icazəsi verin
3. Test bildirişi göndərin
4. Admin panelində fərdi və ümumi bildiriş göndərin
5. Müxtəlif brauzer və cihazlarda test edin

## 📞 Dəstək

Sistem hazırdir və dərhal istifadə oluna bilər. Əlavə suallar üçün repository issues bölməsini istifadə edin.

## 📄 Lisenziya

Bu layihə Apache 2.0 lisenziyası ilə lisenziyalanıb.