<?php

/**
 * API: Acknowledge alert
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/alerts.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$context = get_tenant_context();
$alertId = (int)input('id');

if (alert_acknowledge($alertId, $context['user_id'], $context['tenant_id'])) {
    json_response(['message' => 'Alert acknowledged']);
} else {
    json_response(['error' => 'Alert not found'], 404);
}

