<?php

/**
 * API: Settings Management
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/settings.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $settings = get_settings();
        json_response($settings);
        break;
        
    case 'POST':
        $data = input();
        
        // Validate settings
        $validProviders = array_keys(get_available_map_providers());
        if (isset($data['map_provider']) && !in_array($data['map_provider'], $validProviders)) {
            json_response(['error' => 'Invalid map provider'], 400);
        }
        
        // Validate numeric values
        if (isset($data['map_zoom']) && (!is_numeric($data['map_zoom']) || $data['map_zoom'] < 1 || $data['map_zoom'] > 20)) {
            json_response(['error' => 'Invalid zoom level (must be 1-20)'], 400);
        }
        
        if (isset($data['map_center_lat']) && (!is_numeric($data['map_center_lat']) || $data['map_center_lat'] < -90 || $data['map_center_lat'] > 90)) {
            json_response(['error' => 'Invalid latitude (must be -90 to 90)'], 400);
        }
        
        if (isset($data['map_center_lon']) && (!is_numeric($data['map_center_lon']) || $data['map_center_lon'] < -180 || $data['map_center_lon'] > 180)) {
            json_response(['error' => 'Invalid longitude (must be -180 to 180)'], 400);
        }
        
        // Save settings (global settings, tenant_id = null for admin)
        try {
            if (save_settings($data, null)) {
                json_response(['message' => 'Settings saved successfully', 'settings' => $data]);
            } else {
                json_response(['error' => 'Failed to save settings'], 500);
            }
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

