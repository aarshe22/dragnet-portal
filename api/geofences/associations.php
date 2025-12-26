<?php

/**
 * API: Geofence Device/Group Associations
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/geofences.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();
$geofenceId = isset($_GET['geofence_id']) ? (int)$_GET['geofence_id'] : 0;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

header('Content-Type: application/json');

switch ($method) {
    case 'POST':
        // Add device or group to geofence
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $geofenceId = $geofenceId ?: (int)($data['geofence_id'] ?? 0);
        
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        if ($deviceId || isset($data['device_id'])) {
            $deviceId = $deviceId ?: (int)($data['device_id'] ?? 0);
            if (geofence_add_device($geofenceId, $deviceId, $tenantId)) {
                json_response(['success' => true, 'message' => 'Device added to geofence']);
            } else {
                json_response(['error' => 'Failed to add device'], 500);
            }
        } elseif ($groupId || isset($data['group_id'])) {
            $groupId = $groupId ?: (int)($data['group_id'] ?? 0);
            if (geofence_add_group($geofenceId, $groupId, $tenantId)) {
                json_response(['success' => true, 'message' => 'Group added to geofence']);
            } else {
                json_response(['error' => 'Failed to add group'], 500);
            }
        } else {
            json_response(['error' => 'Device ID or Group ID required'], 400);
        }
        break;
        
    case 'DELETE':
        // Remove device or group from geofence
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        if ($deviceId) {
            if (geofence_remove_device($geofenceId, $deviceId, $tenantId)) {
                json_response(['success' => true, 'message' => 'Device removed from geofence']);
            } else {
                json_response(['error' => 'Failed to remove device'], 500);
            }
        } elseif ($groupId) {
            if (geofence_remove_group($geofenceId, $groupId, $tenantId)) {
                json_response(['success' => true, 'message' => 'Group removed from geofence']);
            } else {
                json_response(['error' => 'Failed to remove group'], 500);
            }
        } else {
            json_response(['error' => 'Device ID or Group ID required'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

