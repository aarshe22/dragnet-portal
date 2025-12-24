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

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':
            $settings = get_settings();
            echo json_encode($settings, JSON_PRETTY_PRINT);
            exit;
            
        case 'POST':
            // Get JSON input
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
                exit;
            }
            
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'No data provided']);
                exit;
            }
            
            // Validate settings
            $validProviders = array_keys(get_available_map_providers());
            if (isset($data['map_provider']) && !in_array($data['map_provider'], $validProviders)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid map provider. Valid options: ' . implode(', ', $validProviders)]);
                exit;
            }
            
            // Validate and sanitize numeric values
            if (isset($data['map_zoom'])) {
                $data['map_zoom'] = (int)$data['map_zoom'];
                if ($data['map_zoom'] < 1 || $data['map_zoom'] > 20) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid zoom level (must be 1-20)']);
                    exit;
                }
            }
            
            if (isset($data['map_center_lat'])) {
                $data['map_center_lat'] = (float)$data['map_center_lat'];
                if ($data['map_center_lat'] < -90 || $data['map_center_lat'] > 90) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid latitude (must be -90 to 90)']);
                    exit;
                }
            }
            
            if (isset($data['map_center_lon'])) {
                $data['map_center_lon'] = (float)$data['map_center_lon'];
                if ($data['map_center_lon'] < -180 || $data['map_center_lon'] > 180) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid longitude (must be -180 to 180)']);
                    exit;
                }
            }
            
            // Save settings (global settings, tenant_id = null for admin)
            if (save_settings($data, null)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Settings saved successfully',
                    'settings' => $data
                ], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save settings (returned false)']);
            }
            exit;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    $errorMsg = 'Database error: ' . $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | Code: ' . $e->getCode() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
        if (isset($e->errorInfo)) {
            $errorMsg .= ' | SQL State: ' . ($e->errorInfo[0] ?? 'N/A');
        }
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    $errorMsg = 'Fatal error: ' . $e->getMessage();
    if ($config['app']['debug']) {
        $errorMsg .= ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
    }
    echo json_encode(['error' => $errorMsg]);
    exit;
}
