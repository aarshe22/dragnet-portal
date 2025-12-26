<?php

/**
 * API: Asset Management (Tenant-scoped)
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/assets.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

switch ($method) {
    case 'GET':
        $assetId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($assetId) {
            $asset = asset_find_with_devices($assetId, $tenantId);
            if (!$asset) {
                json_response(['error' => 'Asset not found'], 404);
            } else {
                json_response($asset);
            }
        } else {
            $assets = asset_list_all($tenantId);
            json_response($assets);
        }
        break;
        
    case 'POST':
        require_role('Operator');
        $data = input();
        $id = asset_create($data, $tenantId);
        json_response(['id' => $id, 'message' => 'Asset created']);
        break;
        
    case 'PUT':
        require_role('Operator');
        $data = input();
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        unset($data['id']);
        if (asset_update($id, $data, $tenantId)) {
            json_response(['message' => 'Asset updated']);
        } else {
            json_response(['error' => 'Update failed'], 400);
        }
        break;
        
    case 'DELETE':
        require_role('Operator');
        $id = (int)input('id');
        if (!$id) {
            json_response(['error' => 'ID required'], 400);
        }
        if (asset_delete($id, $tenantId)) {
            json_response(['message' => 'Asset deleted']);
        } else {
            json_response(['error' => 'Delete failed'], 400);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

