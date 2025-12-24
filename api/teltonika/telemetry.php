<?php

/**
 * API: Receive Teltonika telemetry data
 * 
 * This endpoint receives normalized telemetry data from Teltonika devices.
 * The actual Codec8/8E parsing should be done by a separate service/daemon.
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/devices.php';
require_once __DIR__ . '/../../includes/teltonika.php';

$config = $GLOBALS['config'];
db_init($config['database']);

// Get device identifier (IMEI from header or query param)
$imei = $_SERVER['HTTP_X_IMEI'] ?? $_GET['imei'] ?? null;

if (!$imei) {
    http_response_code(400);
    json_response(['error' => 'IMEI required']);
}

// Find device by IMEI (no tenant scoping for device ingestion)
$device = db_fetch_one("SELECT * FROM devices WHERE imei = :imei", ['imei' => $imei]);

if (!$device) {
    http_response_code(404);
    json_response(['error' => 'Device not found']);
}

// Get telemetry data from request
$telemetryData = input();

// Validate required fields
if (!isset($telemetryData['lat']) || !isset($telemetryData['lon'])) {
    http_response_code(400);
    json_response(['error' => 'Latitude and longitude required']);
}

// Store telemetry
if (teltonika_store_telemetry($device['id'], $telemetryData)) {
    json_response(['success' => true, 'message' => 'Telemetry stored']);
} else {
    http_response_code(500);
    json_response(['error' => 'Failed to store telemetry']);
}

