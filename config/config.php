<?php

/**
 * DragNet Portal Configuration
 */

return [
    'app' => [
        'name' => 'DragNet Portal',
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
    
    'paths' => [
        'base' => __DIR__ . '/..',
        'public' => __DIR__ . '/../public',
        'storage' => __DIR__ . '/../storage',
        'uploads' => __DIR__ . '/../public/uploads',
    ],
    
    'map' => [
        'default_center' => [40.7128, -74.0060], // NYC default
        'default_zoom' => 10,
        'tile_provider' => 'openstreetmap',
    ],
    
    'pwa' => [
        'enabled' => true,
        'name' => 'DragNet Portal',
        'short_name' => 'DragNet',
        'theme_color' => '#0d6efd',
        'background_color' => '#ffffff',
    ],
    
    'push' => [
        'vapid_public_key' => $_ENV['PUSH_VAPID_PUBLIC_KEY'] ?? '',
        'vapid_private_key' => $_ENV['PUSH_VAPID_PRIVATE_KEY'] ?? '',
        'vapid_subject' => $_ENV['PUSH_VAPID_SUBJECT'] ?? 'mailto:admin@dragnet.example.com',
    ],
];

