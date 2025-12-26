<?php

/**
 * API: Device Groups Management
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/device_groups.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        $groupId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($groupId) {
            $group = device_group_find($groupId, $tenantId);
            if (!$group) {
                json_response(['error' => 'Group not found'], 404);
            }
            
            // Get devices in group
            $group['devices'] = device_group_get_devices($groupId, $tenantId);
            json_response($group);
        } else {
            $groups = device_group_list_all($tenantId);
            // Add device counts
            foreach ($groups as &$group) {
                $devices = device_group_get_devices($group['id'], $tenantId);
                $group['device_count'] = count($devices);
            }
            json_response($groups);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        if (empty($data['name'])) {
            json_response(['error' => 'Name is required'], 400);
        }
        
        $groupId = device_group_create($data, $tenantId);
        
        // Add devices if provided
        if (!empty($data['device_ids']) && is_array($data['device_ids'])) {
            foreach ($data['device_ids'] as $deviceId) {
                device_group_add_device($groupId, (int)$deviceId, $tenantId);
            }
        }
        
        json_response(['success' => true, 'id' => $groupId, 'message' => 'Group created']);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $groupId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$groupId) {
            json_response(['error' => 'Group ID required'], 400);
        }
        
        if (device_group_update($groupId, $data, $tenantId)) {
            json_response(['success' => true, 'message' => 'Group updated']);
        } else {
            json_response(['error' => 'Failed to update group'], 500);
        }
        break;
        
    case 'DELETE':
        $groupId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$groupId) {
            json_response(['error' => 'Group ID required'], 400);
        }
        
        if (device_group_delete($groupId, $tenantId)) {
            json_response(['success' => true, 'message' => 'Group deleted']);
        } else {
            json_response(['error' => 'Failed to delete group'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

