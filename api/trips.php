<?php

/**
 * API: Trips Management
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/trips.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        $tripId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;
        
        if ($tripId) {
            // Get single trip with waypoints
            $trip = trip_find_with_waypoints($tripId, $tenantId);
            if (!$trip) {
                json_response(['error' => 'Trip not found'], 404);
            }
            json_response($trip);
        } elseif ($deviceId) {
            // Get trips for device
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $trips = trip_list_by_device($deviceId, $tenantId, $limit);
            json_response($trips);
        } else {
            // Get all trips for tenant
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            
            $where = ["t.tenant_id = :tenant_id"];
            $params = ['tenant_id' => $tenantId];
            
            if ($startDate) {
                $where[] = "DATE(t.start_time) >= :start_date";
                $params['start_date'] = $startDate;
            }
            
            if ($endDate) {
                $where[] = "DATE(t.start_time) <= :end_date";
                $params['end_date'] = $endDate;
            }
            
            $sql = "SELECT t.*, d.device_uid, a.name as asset_name
                    FROM trips t
                    INNER JOIN devices d ON t.device_id = d.id
                    LEFT JOIN assets a ON t.asset_id = a.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY t.start_time DESC
                    LIMIT :limit";
            
            $trips = db_fetch_all($sql, $params);
            json_response($trips);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

