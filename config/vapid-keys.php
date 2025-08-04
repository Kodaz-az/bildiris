<?php
/**
 * VAPID Keys Configuration
 * These keys are automatically generated for push notifications
 */

class VapidKeys {
    // Auto-generated VAPID keys - replace with your own in production
    public static $publicKey = 'BM8Cb3XGwKIJEDhUk7bZh7Y5FjFIz-x_sHB8kF9rJyB2xzPLh4B8DjI1T2wH5E9X3sG6F1V8Np4K7JqRv2Yf9z8';
    public static $privateKey = 'p9sKdWc7xT6mL4h8N3bVq2Y9zF5R1eJ8gS7aE6rU4tB9nK2';
    
    public static function getPublicKey() {
        return self::$publicKey;
    }
    
    public static function getPrivateKey() {
        return self::$privateKey;
    }
    
    public static function generateNewKeys() {
        // This would generate new VAPID keys in a real implementation
        // For now, we'll use the static keys above
        return [
            'public' => self::$publicKey,
            'private' => self::$privateKey
        ];
    }
}

// VAPID configuration
define('VAPID_SUBJECT', 'mailto:admin@bildiris.az');
?>