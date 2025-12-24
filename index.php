<?php

/**
 * DragNet Portal - Main Router (Procedural)
 * 
 * Routes requests to appropriate page files
 */

// Load configuration
$config = require __DIR__ . '/config.php';
$GLOBALS['config'] = $config;

// Load core includes
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize database
db_init($config['database']);

// Initialize session
session_start_custom($config['session']);

// Get request path
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim($path, '/');

// Remove query string
$path = strtok($path, '?');

// Route to page files
$pageMap = [
    '/' => 'dashboard.php',
    '/login' => 'login.php',
    '/login.php' => 'login.php',
    '/logout' => 'logout.php',
    '/logout.php' => 'logout.php',
    '/auth_callback.php' => 'auth_callback.php',
    '/auth/callback' => 'auth_callback.php',
    '/dashboard' => 'dashboard.php',
    '/dashboard.php' => 'dashboard.php',
    '/map' => 'map.php',
    '/map.php' => 'map.php',
    '/devices' => 'devices.php',
    '/devices.php' => 'devices.php',
    '/devices/detail.php' => 'device_detail.php',
    '/assets' => 'assets.php',
    '/assets.php' => 'assets.php',
    '/assets/detail.php' => 'asset_detail.php',
    '/alerts' => 'alerts.php',
    '/alerts.php' => 'alerts.php',
    '/geofences' => 'geofences.php',
    '/geofences.php' => 'geofences.php',
    '/reports' => 'reports.php',
    '/reports.php' => 'reports.php',
    '/admin' => 'admin.php',
    '/admin.php' => 'admin.php',
    '/admin/users' => 'admin_users.php',
    '/admin/users.php' => 'admin_users.php',
];

// Check if it's an API endpoint
if (strpos($path, '/api/') === 0) {
    $apiFile = __DIR__ . '/api' . str_replace('/api', '', $path) . '.php';
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
    http_response_code(404);
    json_response(['error' => 'API endpoint not found']);
}

// Handle PWA files
if ($path === '/manifest.json') {
    require __DIR__ . '/api/pwa/manifest.php';
    exit;
}

if ($path === '/service-worker.js') {
    require __DIR__ . '/api/pwa/service-worker.php';
    exit;
}

// Check if it's a static asset
if (strpos($path, '/public/') === 0) {
    // Let web server handle static files
    return false;
}

// Route to page
$pageFile = $pageMap[$path] ?? null;

if ($pageFile && file_exists(__DIR__ . '/pages/' . $pageFile)) {
    require __DIR__ . '/pages/' . $pageFile;
} else {
    // 404
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Page Not Found</h1></body></html>';
}
