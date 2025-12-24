<?php

/**
 * API: Subscribe to push notifications
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();

$endpoint = input('endpoint');
$p256dh = input('keys.p256dh');
$auth = input('keys.auth');
$platform = input('platform');

if (!$endpoint || !$p256dh || !$auth) {
    json_response(['error' => 'Invalid subscription data'], 400);
}

$context = get_tenant_context();

$existing = db_fetch_one(
    "SELECT id FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
    ['user_id' => $context['user_id'], 'endpoint' => $endpoint]
);

if ($existing) {
    db_execute(
        "UPDATE push_subscriptions SET p256dh_key = :p256dh, auth_key = :auth, platform = :platform, updated_at = NOW() WHERE id = :id",
        [
            'id' => $existing['id'],
            'p256dh' => $p256dh,
            'auth' => $auth,
            'platform' => $platform,
        ]
    );
    json_response(['message' => 'Subscription updated']);
}

db_execute(
    "INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, platform, user_agent) VALUES (:user_id, :endpoint, :p256dh, :auth, :platform, :user_agent)",
    [
        'user_id' => $context['user_id'],
        'endpoint' => $endpoint,
        'p256dh' => $p256dh,
        'auth' => $auth,
        'platform' => $platform,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ]
);

json_response(['message' => 'Subscribed to push notifications']);

