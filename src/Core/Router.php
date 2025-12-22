<?php

namespace DragNet\Core;

/**
 * Simple Router
 * 
 * Matches HTTP method and path to route handlers with parameter extraction.
 */
class Router
{
    private array $routes;
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
    
    /**
     * Match a request to a route
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return array|null Route match with handler and params, or null
     */
    public function match(string $method, string $path): ?array
    {
        $routeKey = "{$method} {$path}";
        
        // Exact match first
        if (isset($this->routes[$routeKey])) {
            return [
                'handler' => $this->routes[$routeKey],
                'params' => []
            ];
        }
        
        // Try parameterized routes
        foreach ($this->routes as $pattern => $handler) {
            [$routeMethod, $routePath] = explode(' ', $pattern, 2);
            
            if ($routeMethod !== $method) {
                continue;
            }
            
            $params = $this->matchPath($routePath, $path);
            if ($params !== null) {
                return [
                    'handler' => $handler,
                    'params' => $params
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Match a parameterized path pattern
     */
    private function matchPath(string $pattern, string $path): ?array
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
}

