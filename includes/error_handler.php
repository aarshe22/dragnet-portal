<?php

/**
 * Error Handler (Procedural)
 * Production-ready error handling
 */

/**
 * Set up error handling
 */
function setup_error_handler(array $config): void
{
    if ($config['app']['debug']) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
    }
    
    set_error_handler('custom_error_handler');
    set_exception_handler('custom_exception_handler');
    register_shutdown_function('custom_shutdown_handler');
}

/**
 * Custom error handler
 */
function custom_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    $config = $GLOBALS['config'] ?? ['app' => ['debug' => false]];
    
    $error = [
        'type' => 'Error',
        'code' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log(json_encode($error));
    
    if ($config['app']['debug']) {
        echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px;'>";
        echo "<strong>Error:</strong> {$errstr}<br>";
        echo "<strong>File:</strong> {$errfile} (line {$errline})<br>";
        echo "</div>";
    }
    
    return true;
}

/**
 * Custom exception handler
 */
function custom_exception_handler(Throwable $exception): void
{
    $config = $GLOBALS['config'] ?? ['app' => ['debug' => false]];
    
    $error = [
        'type' => 'Exception',
        'class' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log(json_encode($error));
    
    if ($config['app']['debug']) {
        http_response_code(500);
        echo "<div style='background: #fee; border: 1px solid #fcc; padding: 20px; margin: 20px;'>";
        echo "<h2>Exception: " . get_class($exception) . "</h2>";
        echo "<p><strong>Message:</strong> {$exception->getMessage()}</p>";
        echo "<p><strong>File:</strong> {$exception->getFile()} (line {$exception->getLine()})</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'An internal error occurred']);
    }
}

/**
 * Shutdown handler for fatal errors
 */
function custom_shutdown_handler(): void
{
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        custom_error_handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

