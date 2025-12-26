<?php

/**
 * Dragnet Intelematics - Main Router (Procedural)
 * 
 * Routes requests to appropriate page files
 */

// Load configuration
$config = require __DIR__ . '/config.php';
$GLOBALS['config'] = $config;

// Setup error handling
require_once __DIR__ . '/includes/error_handler.php';
setup_error_handler($config);

// Get request path first (before loading includes that might need DB)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Normalize empty path to root
if ($path === '' || $path === '/') {
    $path = '/';
} else {
    // Remove trailing slash but keep root slash
    $path = rtrim($path, '/');
    if ($path === '') {
        $path = '/';
    }
}

// Handle root index.php access
if ($path === '/index.php') {
    $path = '/';
}

// Check if it's a static asset (before loading includes)
if (strpos($path, '/public/') === 0) {
    // Let web server handle static files
    return false;
}

// Load core includes
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize database (with error handling)
// Skip DB init for login pages and static files
$skipDbInit = ($path === '/login' || $path === '/login.php' || strpos($path, '/public/') === 0);

if (!$skipDbInit) {
    try {
        db_init($config['database']);
    } catch (Exception $e) {
        // If database fails, redirect to login instead of showing error
        // This allows users to see the login page even if DB is down
        if ($path === '/' || strpos($path, '/dashboard') === 0) {
            header('Location: /login.php');
            exit;
        }
        // For other pages, show error
        if ($config['app']['debug']) {
            die('Database connection error: ' . htmlspecialchars($e->getMessage()) . '<br>Please check your .env file and database configuration.');
        } else {
            die('Database connection error. Please contact administrator.');
        }
    }
}

// Initialize session
session_start_custom($config['session']);

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
    '/geofences/analytics.php' => 'geofences/analytics.php',
    '/trips' => 'trips.php',
    '/trips.php' => 'trips.php',
    '/reports' => 'reports.php',
    '/reports.php' => 'reports.php',
    '/admin' => 'admin.php',
    '/admin.php' => 'admin.php',
    '/admin/users' => 'admin_users.php',
    '/admin/users.php' => 'admin_users.php',
    '/profile' => 'profile.php',
    '/profile.php' => 'profile.php',
    '/settings' => 'settings.php',
    '/settings.php' => 'settings.php',
    '/help' => 'help.php',
    '/help.php' => 'help.php',
    '/register' => 'register.php',
    '/register.php' => 'register.php',
    '/reports' => 'reports.php',
    '/reports.php' => 'reports.php',
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
if ($path === '/manifest.json' || $path === '/public/manifest.json') {
    header('Content-Type: application/manifest+json');
    readfile(__DIR__ . '/public/manifest.json');
    exit;
}

if ($path === '/browserconfig.xml') {
    header('Content-Type: application/xml');
    readfile(__DIR__ . '/browserconfig.xml');
    exit;
}

if ($path === '/service-worker.js') {
    require __DIR__ . '/api/pwa/service-worker.php';
    exit;
}

// Route to page
$pageFile = $pageMap[$path] ?? null;

if ($pageFile && file_exists(__DIR__ . '/pages/' . $pageFile)) {
    // Check if accessing root and not authenticated - redirect to login
    if ($path === '/' && !is_authenticated()) {
        header('Location: /login.php');
        exit;
    }
    require __DIR__ . '/pages/' . $pageFile;
} else {
    // 404 - show debug info if in debug mode
    http_response_code(404);
    if ($config['app']['debug']) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'not set';
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body>';
        echo '<h1>Page Not Found</h1>';
        echo '<p><strong>Path:</strong> ' . htmlspecialchars($path) . '</p>';
        echo '<p><strong>REQUEST_URI:</strong> ' . htmlspecialchars($requestUri) . '</p>';
        echo '<p><strong>Available routes:</strong> ' . htmlspecialchars(implode(', ', array_keys($pageMap))) . '</p>';
        echo '<p><strong>Page file:</strong> ' . htmlspecialchars($pageFile ?? 'null') . '</p>';
        if ($pageFile) {
            $fullPath = __DIR__ . '/pages/' . $pageFile;
            echo '<p><strong>File exists:</strong> ' . (file_exists($fullPath) ? 'Yes' : 'No') . '</p>';
            echo '<p><strong>Full path:</strong> ' . htmlspecialchars($fullPath) . '</p>';
        }
        echo '</body></html>';
    } else {
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Page Not Found</h1></body></html>';
    }
}
