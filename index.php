<?php

/**
 * DragNet Portal - Front Controller
 * 
 * Single entry point for all requests. Handles routing, authentication,
 * tenant isolation, and dispatches to appropriate controllers.
 */

require_once __DIR__ . '/vendor/autoload.php';

use DragNet\Core\Application;
use DragNet\Core\Router;
use DragNet\Core\Database;
use DragNet\Core\Session;
use DragNet\Core\TenantContext;
use DragNet\Core\EnvLoader;

// Load .env file if it exists
EnvLoader::load(__DIR__);

// Load configuration
$config = require __DIR__ . '/config/config.php';

// Initialize session
Session::start($config['session']);

// Initialize database connection
$db = Database::getInstance($config['database']);

// Initialize application
$app = new Application($config, $db);

// Load routes
$routes = require __DIR__ . '/config/routes.php';
$router = new Router($routes);

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
    $route = $router->match($method, $path);
    
    if (!$route) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    
    // Extract controller and method
    [$controllerName, $methodName] = explode('@', $route['handler']);
    
    // Resolve tenant context (from session or SSO)
    $tenantContext = TenantContext::fromSession();
    
    if (!$tenantContext && !in_array($path, ['/login', '/auth/callback', '/auth/saml', '/auth/oauth'])) {
        // Redirect to login if not authenticated
        header('Location: /login');
        exit;
    }
    
    // Set tenant context in application
    if ($tenantContext) {
        $app->setTenantContext($tenantContext);
    }
    
    // Load controller
    $controllerClass = "DragNet\\Controllers\\{$controllerName}";
    if (!class_exists($controllerClass)) {
        throw new Exception("Controller {$controllerClass} not found");
    }
    
    $controller = new $controllerClass($app);
    
    // Check if method exists
    if (!method_exists($controller, $methodName)) {
        throw new Exception("Method {$methodName} not found in {$controllerClass}");
    }
    
    // Execute controller method with route parameters
    $response = $controller->$methodName($route['params'] ?? []);
    
    // Output response
    if (is_array($response) || is_object($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo $response;
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    
    // Try to get config for debug setting, but handle if config fails
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

