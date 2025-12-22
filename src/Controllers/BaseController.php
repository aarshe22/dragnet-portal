<?php

namespace DragNet\Controllers;

use DragNet\Core\Application;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers.
 */
abstract class BaseController
{
    protected Application $app;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Require tenant context (throws if not set)
     */
    protected function requireTenant(): int
    {
        return $this->app->requireTenant();
    }
    
    /**
     * Require minimum role level
     */
    protected function requireRole(string $role): void
    {
        $context = $this->app->getTenantContext();
        if (!$context || !$context->hasRole($role)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
    
    /**
     * Render a view
     */
    protected function view(string $template, array $data = []): string
    {
        extract($data);
        $app = $this->app; // Make app available to views
        $templatePath = __DIR__ . '/../Views/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("View template not found: {$template}");
        }
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, int $statusCode = 200): array
    {
        http_response_code($statusCode);
        return $data;
    }
    
    /**
     * Get request input
     */
    protected function input(string $key = null, $default = null)
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $merged = array_merge($_GET, $_POST, $input);
        
        if ($key === null) {
            return $merged;
        }
        
        return $merged[$key] ?? $default;
    }
}

