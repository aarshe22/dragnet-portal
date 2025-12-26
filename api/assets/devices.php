<?php

/**
 * API: Asset Device Linking (Link/Unlink devices to assets)
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/assets.php';
require_once __DIR__ . '/../../includes/devices.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

switch ($method) {
    case 'POST':
        // Link device(s) to asset
        $data = input();
        $assetId = (int)($data['asset_id'] ?? 0);
        $deviceIds = $data['device_ids'] ?? [];
        
        if (!$assetId) {
            json_response(['error' => 'Asset ID required'], 400);
        }
        
        if (empty($deviceIds) || !is_array($deviceIds)) {
            json_response(['error' => 'Device IDs required'], 400);
        }
        
        // Verify asset exists and belongs to tenant
        $asset = asset_find($assetId, $tenantId);
        if (!$asset) {
            json_response(['error' => 'Asset not found'], 404);
        }
        
        $linked = [];
        $errors = [];
        
        foreach ($deviceIds as $deviceId) {
            $deviceId = (int)$deviceId;
            // Verify device exists and belongs to tenant
            $device = device_find($deviceId, $tenantId);
            if (!$device) {
                $errors[] = "Device {$deviceId} not found";
                continue;
            }
            
            // Link device to asset
            if (device_update($deviceId, ['asset_id' => $assetId], $tenantId)) {
                $linked[] = $deviceId;
            } else {
                $errors[] = "Failed to link device {$deviceId}";
            }
        }
        
        if (!empty($linked)) {
            json_response([
                'message' => count($linked) . ' device(s) linked',
                'linked' => $linked,
                'errors' => $errors
            ]);
        } else {
            json_response(['error' => 'No devices linked', 'errors' => $errors], 400);
        }
        break;
        
    case 'DELETE':
        // Unlink device(s) from asset
        $data = input();
        $deviceIds = $data['device_ids'] ?? [];
        
        if (empty($deviceIds) || !is_array($deviceIds)) {
            json_response(['error' => 'Device IDs required'], 400);
        }
        
        $unlinked = [];
        $errors = [];
        
        foreach ($deviceIds as $deviceId) {
            $deviceId = (int)$deviceId;
            // Verify device exists and belongs to tenant
            $device = device_find($deviceId, $tenantId);
            if (!$device) {
                $errors[] = "Device {$deviceId} not found";
                continue;
            }
            
            // Unlink device from asset
            if (device_update($deviceId, ['asset_id' => null], $tenantId)) {
                $unlinked[] = $deviceId;
            } else {
                $errors[] = "Failed to unlink device {$deviceId}";
            }
        }
        
        if (!empty($unlinked)) {
            json_response([
                'message' => count($unlinked) . ' device(s) unlinked',
                'unlinked' => $unlinked,
                'errors' => $errors
            ]);
        } else {
            json_response(['error' => 'No devices unlinked', 'errors' => $errors], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

