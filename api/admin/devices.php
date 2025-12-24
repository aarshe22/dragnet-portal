<?php

/**
 * API: Device Management
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/admin.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $filters = [];
        if (isset($_GET['tenant_id'])) {
            $filters['tenant_id'] = (int)$_GET['tenant_id'];
        }
        if (isset($_GET['imei'])) {
            $filters['imei'] = $_GET['imei'];
        }
        $devices = admin_get_devices($filters);
        json_response($devices);
        break;
        
    case 'POST':
        $data = input();
        $id = admin_create_device($data);
        json_response(['id' => $id, 'message' => 'Device created']);
        break;
        
    case 'PUT':
        $data = input();
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        unset($data['id']);
        if (admin_update_device($id, $data)) {
            json_response(['message' => 'Device updated']);
        } else {
            json_response(['error' => 'Update failed'], 400);
        }
        break;
        
    case 'DELETE':
        $id = (int)input('id');
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        if (admin_delete_device($id)) {
            json_response(['message' => 'Device deleted']);
        } else {
            json_response(['error' => 'Delete failed'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

