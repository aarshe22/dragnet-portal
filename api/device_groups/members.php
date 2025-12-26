<?php

/**
 * API: Device Group Members Management
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/device_groups.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();
$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;

header('Content-Type: application/json');

switch ($method) {
    case 'POST':
        // Add device to group
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $groupId = $groupId ?: (int)($data['group_id'] ?? 0);
        $deviceId = $deviceId ?: (int)($data['device_id'] ?? 0);
        
        if (!$groupId || !$deviceId) {
            json_response(['error' => 'Group ID and Device ID required'], 400);
        }
        
        if (device_group_add_device($groupId, $deviceId, $tenantId)) {
            json_response(['success' => true, 'message' => 'Device added to group']);
        } else {
            json_response(['error' => 'Failed to add device to group'], 500);
        }
        break;
        
    case 'DELETE':
        // Remove device from group
        if (!$groupId || !$deviceId) {
            json_response(['error' => 'Group ID and Device ID required'], 400);
        }
        
        if (device_group_remove_device($groupId, $deviceId, $tenantId)) {
            json_response(['success' => true, 'message' => 'Device removed from group']);
        } else {
            json_response(['error' => 'Failed to remove device from group'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

