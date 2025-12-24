<?php

/**
 * DragNet Portal - Front Controller (Procedural)
 * 
 * Single entry point for all requests. Handles routing and dispatches to functions.
 */

// Load all includes
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/models.php';
require_once __DIR__ . '/includes/controllers.php';
require_once __DIR__ . '/includes/controllers_remaining.php';
require_once __DIR__ . '/includes/teltonika.php';

// Load .env file if it exists
load_env(__DIR__);

// Load configuration
$config = require __DIR__ . '/config/config.php';
$GLOBALS['config'] = $config;

// Initialize database connection
db_init($config['database']);

// Initialize session
session_start_custom($config['session']);

// Load routes
$routes = require __DIR__ . '/config/routes.php';

// Get current request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
// Handle method override for PUT/DELETE via POST or header
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
} elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Route the request
try {
    $route = find_route($method, $path, $routes);
    
    if (!$route) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    
    // Get handler function name
    $handler = $route['handler'];
    
    // Resolve tenant context (from session)
    $tenantContext = get_tenant_context();
    
    if (!$tenantContext && !in_array($path, ['/login', '/auth/callback', '/auth/saml', '/auth/oauth'])) {
        // Redirect to login if not authenticated
        redirect('/login');
    }
    
    // Check if handler is a function
    if (!function_exists($handler)) {
        throw new Exception("Handler function {$handler} not found");
    }
    
    // Execute handler function with route parameters
    $response = $handler($route['params'] ?? []);
    
    // Output response
    if (is_string($response)) {
        // HTML view
        echo $response;
    } elseif (is_array($response) || is_object($response)) {
        // JSON response (if function didn't exit)
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    // If function called json_response() or redirect(), it already exited
    
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    
    // Try to get config for debug setting
    $debug = false;
    try {
        if (isset($config) && isset($config['app']['debug'])) {
            $debug = $config['app']['debug'];
        }
    } catch (Exception $configError) {
        // Config failed, use defaults
    }
    
    if ($debug) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        // Check if this is an HTML request or API request
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'text/html') !== false) {
            // HTML error page
            echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
            echo '<h1>Internal Server Error</h1>';
            echo '<p>An error occurred. Please contact the administrator.</p>';
            echo '</body></html>';
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}
