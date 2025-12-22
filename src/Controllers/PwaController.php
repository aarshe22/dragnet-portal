<?php

namespace DragNet\Controllers;

/**
 * PWA Controller
 * 
 * Serves PWA manifest and service worker
 */
class PwaController extends BaseController
{
    /**
     * Serve PWA manifest
     */
    public function manifest(): array
    {
        $config = $this->app->getConfig();
        $pwaConfig = $config['pwa'];
        
        header('Content-Type: application/manifest+json');
        
        return [
            'name' => $pwaConfig['name'],
            'short_name' => $pwaConfig['short_name'],
            'description' => 'DragNet Telematics Portal',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $pwaConfig['background_color'],
            'theme_color' => $pwaConfig['theme_color'],
            'orientation' => 'any',
            'icons' => [
                [
                    'src' => '/public/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/public/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ];
    }
    
    /**
     * Serve service worker
     */
    public function serviceWorker(): string
    {
        header('Content-Type: application/javascript');
        
        return <<<'JS'
const CACHE_NAME = 'dragnet-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/',
                '/offline.html',
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
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
    } else {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request);
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
JS;
    }
}

