const CACHE_NAME = 'dragnet-v1';
const OFFLINE_URL = '/public/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/',
                '/public/offline.html',
                '/public/css/app.css',
                '/public/js/app.js',
            ]);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Don't cache API requests - always fetch from network
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(event.request).catch((error) => {
                // Return a proper error response instead of undefined
                return new Response(JSON.stringify({ error: 'Network error' }), {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: { 'Content-Type': 'application/json' }
                });
            })
        );
        return;
    }
    
    // Handle navigation requests
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL).then((response) => {
                    return response || new Response('Offline', { status: 503 });
                });
            })
        );
    } else {
        // For other requests, try network first, then cache
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request).then((response) => {
                    return response || new Response('Not found in cache', { status: 404 });
                });
            })
        );
    }
});

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'DragNet Notification';
    const options = {
        body: data.body || '',
        icon: '/public/icons/icon-192.png',
        badge: '/public/icons/icon-192.png',
        data: data.url || '/',
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data || '/')
    );
});

