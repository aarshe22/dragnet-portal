<?php

/**
 * API: Get VAPID public key for push notifications
 */

// Load configuration
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

// Return VAPID public key if configured
if (isset($config['push']['vapid_public_key']) && !empty($config['push']['vapid_public_key'])) {
    json_response([
        'publicKey' => $config['push']['vapid_public_key']
    ]);
} else {
    json_response([
        'error' => 'VAPID keys not configured'
    ], 400);
}

