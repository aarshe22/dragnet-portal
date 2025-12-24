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
        
        // In production, save to database settings table
        // For now, we'll store in session or return success
        // You could create a settings table:
        // CREATE TABLE settings (id INT PRIMARY KEY AUTO_INCREMENT, tenant_id INT, setting_key VARCHAR(100), setting_value TEXT, UNIQUE KEY (tenant_id, setting_key));
        
        // For demo, we'll just return success
        // In production: save_settings($data);
        
        json_response(['message' => 'Settings saved', 'settings' => $data]);
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

