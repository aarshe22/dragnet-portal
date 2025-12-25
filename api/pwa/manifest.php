<?php

/**
 * PWA Manifest
 */

require_once __DIR__ . '/../../config.php';

$config = require __DIR__ . '/../../config.php';
$pwaConfig = $config['pwa'];

header('Content-Type: application/manifest+json');

echo json_encode([
    'name' => $pwaConfig['name'],
    'short_name' => $pwaConfig['short_name'],
    'description' => 'Dragnet Intelematics Portal',
    'start_url' => '/',
    'display' => 'standalone',
    'background_color' => $pwaConfig['background_color'],
    'theme_color' => $pwaConfig['theme_color'],
    'orientation' => 'any',
    'icons' => [
        ['src' => '/public/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => '/public/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
    ],
]);

