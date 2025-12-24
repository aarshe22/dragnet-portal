<?php

/**
 * API: User Management
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/admin.php';

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
        if (isset($_GET['email'])) {
            $filters['email'] = $_GET['email'];
        }
        $users = admin_get_users($filters);
        json_response($users);
        break;
        
    case 'POST':
        $data = input();
        $id = admin_create_user($data);
        json_response(['id' => $id, 'message' => 'User created']);
        break;
        
    case 'PUT':
        $data = input();
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        unset($data['id']);
        if (admin_update_user($id, $data)) {
            json_response(['message' => 'User updated']);
        } else {
            json_response(['error' => 'Update failed'], 400);
        }
        break;
        
    case 'DELETE':
        $id = (int)input('id');
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        if (admin_delete_user($id)) {
            json_response(['message' => 'User deleted']);
        } else {
            json_response(['error' => 'Delete failed'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

