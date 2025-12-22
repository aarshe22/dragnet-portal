<?php

namespace DragNet\Core;

/**
 * Session Management
 */
class Session
{
    public static function start(array $config): void
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
    
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    public static function destroy(): void
    {
        session_destroy();
    }
}

