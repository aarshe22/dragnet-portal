<?php

/**
 * API: Geofence Analytics
 * Get geofence visit statistics, dwell times, and frequency
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
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        if (!$geofenceId) {
            json_response(['error' => 'Geofence ID required'], 400);
        }
        
        $analytics = geofence_get_analytics($geofenceId, $tenantId, $startDate, $endDate);
        json_response($analytics);
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

