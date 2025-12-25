<?php

/**
 * Dragnet Intelematics Configuration
 */

// Load .env file if it exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

return [
    'app' => [
        'name' => 'Dragnet Intelematics',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'name' => $_ENV['DB_NAME'] ?? 'dragnet',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
    ],
    
    'session' => [
        'name' => 'DRAGNET_SESSION',
        'lifetime' => 3600,
        'secure' => ($_ENV['SESSION_SECURE'] ?? 'true') === 'true',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    
    'sso' => [
        'enabled' => true,
        'providers' => [
            'entra' => [
                'enabled' => ($_ENV['SSO_ENTRA_ENABLED'] ?? 'false') === 'true',
                'client_id' => $_ENV['SSO_ENTRA_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['SSO_ENTRA_CLIENT_SECRET'] ?? '',
                'tenant_id' => $_ENV['SSO_ENTRA_TENANT_ID'] ?? '',
                'redirect_uri' => $_ENV['SSO_ENTRA_REDIRECT_URI'] ?? '',
            ],
            'google' => [
                'enabled' => ($_ENV['SSO_GOOGLE_ENABLED'] ?? 'false') === 'true',
                'client_id' => $_ENV['SSO_GOOGLE_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['SSO_GOOGLE_CLIENT_SECRET'] ?? '',
                'redirect_uri' => $_ENV['SSO_GOOGLE_REDIRECT_URI'] ?? '',
            ],
        ],
    ],
    
    'map' => [
        'default_center' => [40.7128, -74.0060],
        'default_zoom' => 10,
        'tile_provider' => 'openstreetmap',
    ],
    
    'pwa' => [
        'enabled' => true,
        'name' => 'Dragnet Intelematics',
        'short_name' => 'DragNet',
        'theme_color' => '#1a1a1a',
        'background_color' => '#ffffff',
    ],
    
    'push' => [
        'vapid_public_key' => $_ENV['PUSH_VAPID_PUBLIC_KEY'] ?? '',
        'vapid_private_key' => $_ENV['PUSH_VAPID_PRIVATE_KEY'] ?? '',
        'vapid_subject' => $_ENV['PUSH_VAPID_SUBJECT'] ?? 'mailto:admin@dragnet.example.com',
    ],
    
    'device' => [
        'online_threshold_minutes' => 15,
        'moving_speed_threshold' => 5, // km/h
        'idle_speed_threshold' => 0.5, // km/h
    ],
];

