<?php

/**
 * API: Get dashboard widgets data
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/devices.php';
require_once __DIR__ . '/../../includes/assets.php';
require_once __DIR__ . '/../../includes/alerts.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$tenantId = require_tenant();

$totalDevices = device_count_by_status('online', $tenantId) + device_count_by_status('offline', $tenantId);
$onlineDevices = device_count_by_status('online', $tenantId);
$offlineDevices = device_count_by_status('offline', $tenantId);
$totalAssets = asset_count($tenantId);
$activeAlerts = alert_count(['acknowledged' => false], $tenantId);
$criticalAlerts = alert_count(['acknowledged' => false, 'severity' => 'critical'], $tenantId);

$devices = device_list_all($tenantId);
$moving = 0;
$idle = 0;
$parked = 0;

foreach ($devices as $device) {
    device_update_status($device['id'], $tenantId);
    $device = device_find($device['id'], $tenantId);
    
    if ($device['status'] === 'moving') {
        $moving++;
    } elseif ($device['status'] === 'idle') {
        $idle++;
    } elseif ($device['status'] === 'parked') {
        $parked++;
    }
}

json_response([
    'total_devices' => $totalDevices,
    'online_devices' => $onlineDevices,
    'offline_devices' => $offlineDevices,
    'total_assets' => $totalAssets,
    'active_alerts' => $activeAlerts,
    'critical_alerts' => $criticalAlerts,
    'moving' => $moving,
    'idle' => $idle,
    'parked' => $parked,
]);

