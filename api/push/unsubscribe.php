<?php

/**
 * API: Unsubscribe from push notifications
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();

$endpoint = input('endpoint');
if (!$endpoint) {
    json_response(['error' => 'Endpoint required'], 400);
}

$context = get_tenant_context();

db_execute(
    "DELETE FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
    ['user_id' => $context['user_id'], 'endpoint' => $endpoint]
);

json_response(['message' => 'Unsubscribed from push notifications']);

