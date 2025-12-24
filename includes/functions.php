<?php

/**
 * General Helper Functions (Procedural)
 */

/**
 * Get request input (GET, POST, or JSON body)
 */
function input(?string $key = null, $default = null)
{
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $merged = array_merge($_GET, $_POST, $input);
    
    if ($key === null) {
        return $merged;
    }
    
    return $merged[$key] ?? $default;
}

/**
 * Return JSON response and exit
 */
function json_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect and exit
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Render view template
 */
function render_view(string $template, array $data = []): string
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
 * Include header template
 */
function include_header(array $data = []): void
{
    extract($data);
    $headerPath = __DIR__ . '/../views/header.php';
    if (file_exists($headerPath)) {
        include $headerPath;
    }
}

/**
 * Include footer template
 */
function include_footer(array $data = []): void
{
    extract($data);
    $footerPath = __DIR__ . '/../views/footer.php';
    if (file_exists($footerPath)) {
        include $footerPath;
    }
}

/**
 * Escape HTML
 */
function h(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date/time
 */
function format_datetime(?string $datetime, string $format = 'Y-m-d H:i:s'): string
{
    if (!$datetime) {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get current page name from script path
 */
function get_current_page(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $page = basename($script, '.php');
    
    // Handle index.php routing
    if ($page === 'index') {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = rtrim($path, '/');
        if ($path === '' || $path === '/') {
            return 'dashboard';
        }
        $page = basename($path, '.php');
    }
    
    return $page;
}

