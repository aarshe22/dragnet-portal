<?php

/**
 * API: Get devices for map display
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/devices.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$tenantId = require_tenant();
$devices = device_list_with_status($tenantId);

require_once __DIR__ . '/../../includes/device_types.php';

$result = [];
foreach ($devices as $device) {
    $deviceType = $device['device_type'] ?? 'vehicle';
    $typeConfig = get_device_type_config($deviceType);
    
    $result[] = [
        'id' => $device['id'],
        'device_uid' => $device['device_uid'],
        'asset_id' => $device['asset_id'],
        'status' => $device['status'],
        'device_type' => $deviceType,
        'device_type_icon' => $typeConfig['icon'] ?? 'fa-car',
        'device_type_label' => $typeConfig['label'] ?? 'Vehicle',
        'lat' => $device['lat'] ? (float)$device['lat'] : null,
        'lon' => $device['lon'] ? (float)$device['lon'] : null,
        'speed' => $device['speed'] ? (float)$device['speed'] : null,
        'last_seen' => $device['last_seen'],
        'gsm_signal' => $device['gsm_signal'],
        'external_voltage' => $device['external_voltage'],
        'internal_battery' => $device['internal_battery'],
    ];
}

json_response($result);

