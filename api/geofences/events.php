<?php

/**
 * API: Geofence Events
 * Get geofence entry/exit events and analytics
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
require_role('ReadOnly');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        $geofenceId = isset($_GET['geofence_id']) ? (int)$_GET['geofence_id'] : 0;
        $deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        $events = geofence_get_events($geofenceId, $tenantId, $deviceId, $startDate, $endDate, $limit);
        json_response($events);
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

