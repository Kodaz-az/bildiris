/**
 * Service Worker for Bildiris Push Notification System
 */

const CACHE_NAME = 'bildiris-v1';
const urlsToCache = [
    './',
    './index.php',
    './css/style.css',
    './js/main.js'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
            .catch((error) => {
                console.error('Failed to cache resources:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
            .catch((error) => {
                console.error('Fetch failed:', error);
                throw error;
            })
    );
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    console.log('Push event received:', event);
    
    let notificationData = {
        title: 'Bildiris',
        body: 'Yeni bildirişiniz var',
        icon: 'images/icon-192x192.png',
        badge: 'images/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Oxu',
                icon: 'images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Bağla',
                icon: 'images/xmark.png'
            }
        ]
    };

    // Parse push data if available
    if (event.data) {
        try {
            const pushData = event.data.json();
            notificationData = {
                ...notificationData,
                title: pushData.title || notificationData.title,
                body: pushData.body || pushData.message || notificationData.body,
                icon: pushData.icon || notificationData.icon,
                data: {
                    ...notificationData.data,
                    url: pushData.url || './',
                    ...pushData.data
                }
            };
        } catch (error) {
            console.error('Error parsing push data:', error);
            notificationData.body = event.data.text();
        }
    }

    const promiseChain = self.registration.showNotification(
        notificationData.title,
        {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            vibrate: notificationData.vibrate,
            data: notificationData.data,
            actions: notificationData.actions,
            requireInteraction: true,
            tag: 'bildiris-notification'
        }
    );

    event.waitUntil(promiseChain);
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('Notification click received:', event);
    
    event.notification.close();

    if (event.action === 'close') {
        // User clicked close, just close the notification
        return;
    }

    // Handle notification click or 'explore' action
    const urlToOpen = event.notification.data?.url || './';
    
    const promiseChain = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then((clientList) => {
        // Check if there's already a window/tab open with the target URL
        for (let i = 0; i < clientList.length; i++) {
            const client = clientList[i];
            if (client.url === urlToOpen && 'focus' in client) {
                return client.focus();
            }
        }
        
        // If not, open a new window/tab
        if (clients.openWindow) {
            return clients.openWindow(urlToOpen);
        }
    });

    event.waitUntil(promiseChain);
});

// Background sync event (for offline functionality)
self.addEventListener('sync', (event) => {
    console.log('Background sync event:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    try {
        // Perform any background sync operations here
        console.log('Performing background sync...');
        
        // For example, sync pending notifications or user data
        // This is a placeholder for actual sync logic
        
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Error handling
self.addEventListener('error', (event) => {
    console.error('Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
    console.error('Service Worker unhandled rejection:', event.reason);
});

// Message event - communicate with main thread
self.addEventListener('message', (event) => {
    console.log('Service Worker received message:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    // Send response back to main thread
    event.ports[0].postMessage({
        error: null,
        data: 'Message received'
    });
});

// Periodic background sync (if supported)
self.addEventListener('periodicsync', (event) => {
    console.log('Periodic sync event:', event.tag);
    
    if (event.tag === 'content-sync') {
        event.waitUntil(doPeriodicSync());
    }
});

async function doPeriodicSync() {
    try {
        // Perform periodic sync operations
        console.log('Performing periodic sync...');
        
        // This could be used to check for new notifications
        // or update cached content
        
    } catch (error) {
        console.error('Periodic sync failed:', error);
    }
}

console.log('Service Worker loaded successfully');