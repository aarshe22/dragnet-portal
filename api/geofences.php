<?php

/**
 * API: Geofences Management
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/geofences.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        $geofenceId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($geofenceId) {
            $geofence = geofence_find($geofenceId, $tenantId);
            if (!$geofence) {
                json_response(['error' => 'Geofence not found'], 404);
            }
            
            // Get associated devices and groups
            $geofence['devices'] = geofence_get_devices($geofenceId, $tenantId);
            $geofence['groups'] = geofence_get_groups($geofenceId, $tenantId);
            json_response($geofence);
        } else {
            $geofences = geofence_list_all($tenantId);
            json_response($geofences);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        if (empty($data['name']) || empty($data['type']) || empty($data['coordinates'])) {
            json_response(['error' => 'Name, type, and coordinates are required'], 400);
        }
        
        $geofenceId = geofence_create($data, $tenantId);
        
        // Add devices if provided
        if (!empty($data['device_ids']) && is_array($data['device_ids'])) {
            foreach ($data['device_ids'] as $deviceId) {
                geofence_add_device($geofenceId, (int)$deviceId, $tenantId);
            }
        }
        
        // Add groups if provided
        if (!empty($data['group_ids']) && is_array($data['group_ids'])) {
            foreach ($data['group_ids'] as $groupId) {
                geofence_add_group($geofenceId, (int)$groupId, $tenantId);
            }
        }
        
        json_response(['success' => true, 'id' => $geofenceId, 'message' => 'Geofence created']);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $geofenceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        if (geofence_update($geofenceId, $data, $tenantId)) {
            json_response(['success' => true, 'message' => 'Geofence updated']);
        } else {
            json_response(['error' => 'Failed to update geofence'], 500);
        }
        break;
        
    case 'DELETE':
        $geofenceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        if (geofence_delete($geofenceId, $tenantId)) {
            json_response(['success' => true, 'message' => 'Geofence deleted']);
        } else {
            json_response(['error' => 'Failed to delete geofence'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

