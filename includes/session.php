<?php

/**
 * Session Functions (Procedural)
 */

/**
 * Start session with configuration
 */
function session_start_custom(array $config): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    session_name($config['name']);
    
    $options = [
        'cookie_lifetime' => $config['lifetime'],
        'cookie_secure' => $config['secure'],
        'cookie_httponly' => $config['httponly'],
        'cookie_samesite' => $config['samesite'],
    ];
    
    session_start($options);
}

/**
 * Get session value
 */
function session_get(string $key, $default = null)
{
    return $_SESSION[$key] ?? $default;
}

/**
 * Set session value
 */
function session_set(string $key, $value): void
{
    $_SESSION[$key] = $value;
}

/**
 * Check if session key exists
 */
function session_has(string $key): bool
{
    return isset($_SESSION[$key]);
}

/**
 * Remove session key
 */
function session_remove(string $key): void
{
    unset($_SESSION[$key]);
}

/**
 * Destroy session
 */
function session_destroy_custom(): void
{
    session_destroy();
}

