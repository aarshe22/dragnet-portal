const CACHE_NAME = 'dragnet-v1';
const OFFLINE_URL = '/public/offline.html';
const RUNTIME_CACHE = 'dragnet-runtime-v1';

// Assets to cache on install
const STATIC_CACHE_URLS = [
    '/',
    '/public/offline.html',
    '/public/css/app.css',
    '/public/css/dragnet-theme.css',
    '/public/js/app.js',
    '/public/icons/icon-192.png',
    '/public/icons/icon-512.png',
    '/public/manifest.json'
];

self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Service Worker: Caching static assets');
            return cache.addAll(STATIC_CACHE_URLS.map(url => {
                try {
                    return new Request(url, { cache: 'reload' });
                } catch (e) {
                    return url;
                }
            })).catch(err => {
                console.log('Service Worker: Some assets failed to cache', err);
                // Continue even if some assets fail
                return Promise.resolve();
            });
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                        console.log('Service Worker: Deleting old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
    console.log('Service Worker: Activated');
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

