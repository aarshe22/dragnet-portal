<?php

/**
 * General Helper Functions
 */

/**
 * Load .env file
 */
function load_env(string $path): void
{
    $envFile = $path . '/.env';
    
    if (!file_exists($envFile)) {
        return;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            // Set environment variable if not already set
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

/**
 * Get request input
 */
function input(string $key = null, $default = null)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $merged = array_merge($_GET, $_POST, $input);
    
    if ($key === null) {
        return $merged;
    }
    
    return $merged[$key] ?? $default;
}

/**
 * Render view
 */
function view(string $template, array $data = []): string
{
    extract($data);
    $templatePath = __DIR__ . '/../views/' . $template . '.php';
    
    if (!file_exists($templatePath)) {
        throw new Exception("View template not found: {$template}");
    }
    
    ob_start();
    include $templatePath;
    return ob_get_clean();
}

/**
 * Return JSON response
 */
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Match route pattern
 */
function match_route(string $pattern, string $path): ?array
{
    // Convert route pattern to regex
    $regex = preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    
    if (preg_match($regex, $path, $matches)) {
        // Extract named parameters
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    return null;
}

/**
 * Find matching route
 */
function find_route(string $method, string $path, array $routes): ?array
{
    $routeKey = "{$method} {$path}";
    
    // Exact match first
    if (isset($routes[$routeKey])) {
        return [
            'handler' => $routes[$routeKey],
            'params' => []
        ];
    }
    
    // Try parameterized routes
    foreach ($routes as $pattern => $handler) {
        [$routeMethod, $routePath] = explode(' ', $pattern, 2);
        
        if ($routeMethod !== $method) {
            continue;
        }
        
        $params = match_route($routePath, $path);
        if ($params !== null) {
            return [
                'handler' => $handler,
                'params' => $params
            ];
        }
    }
    
    return null;
}

