<?php

/**
 * API: User Alert Subscriptions
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/user_alert_subscriptions.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();
$context = get_tenant_context();
$userId = $context['user_id'];

switch ($method) {
    case 'GET':
        $subscriptions = user_alert_subscriptions_get($userId, $tenantId);
        json_response($subscriptions);
        break;
        
    case 'POST':
        require_role('Operator');
        $data = input();
        $id = user_alert_subscription_save($data, $userId, $tenantId);
        json_response(['id' => $id, 'message' => 'Subscription saved']);
        break;
        
    case 'DELETE':
        require_role('Operator');
        $id = (int)input('id');
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        if (user_alert_subscription_delete($id, $userId, $tenantId)) {
            json_response(['message' => 'Subscription deleted']);
        } else {
            json_response(['error' => 'Delete failed'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

