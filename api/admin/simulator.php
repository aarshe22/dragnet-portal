<?php

/**
 * API: Teltonika Telemetry Simulator Control
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/teltonika_simulator.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'send':
        // Send a single telemetry packet
        $deviceId = (int)($_POST['device_id'] ?? $_GET['device_id'] ?? 0);
        $config = [
            'speed' => isset($_POST['speed']) ? (float)$_POST['speed'] : null,
            'moving' => isset($_POST['moving']) ? (bool)$_POST['moving'] : null,
            'route' => $_POST['route'] ?? 'random',
            'interval' => (int)($_POST['interval'] ?? 30),
        ];
        
        if (!$deviceId) {
            json_response(['error' => 'Device ID required'], 400);
        }
        
        try {
            $telemetry = teltonika_simulator_generate_telemetry($deviceId, $config);
            $device = db_fetch_one("SELECT imei FROM devices WHERE id = :id", ['id' => $deviceId]);
            
            if (teltonika_simulator_send_telemetry($device['imei'], $telemetry)) {
                json_response([
                    'success' => true,
                    'message' => 'Telemetry sent',
                    'telemetry' => $telemetry
                ]);
            } else {
                json_response(['error' => 'Failed to send telemetry'], 500);
            }
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 500);
        }
        break;
        
    case 'stream':
        // Stream telemetry continuously
        $deviceId = (int)($_POST['device_id'] ?? $_GET['device_id'] ?? 0);
        $iterations = isset($_POST['iterations']) ? (int)$_POST['iterations'] : null;
        $config = [
            'speed' => isset($_POST['speed']) ? (float)$_POST['speed'] : null,
            'moving' => isset($_POST['moving']) ? (bool)$_POST['moving'] : null,
            'route' => $_POST['route'] ?? 'random',
            'interval' => (int)($_POST['interval'] ?? 30),
        ];
        
        if (!$deviceId) {
            json_response(['error' => 'Device ID required'], 400);
        }
        
        try {
            $results = teltonika_simulator_run($deviceId, $config, $iterations);
            json_response([
                'success' => true,
                'message' => 'Simulation completed',
                'results' => $results
            ]);
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 500);
        }
        break;
        
    case 'devices':
        // Get list of devices for simulator
        $context = get_tenant_context();
        $devices = db_fetch_all(
            "SELECT id, device_uid, imei, name, status FROM devices WHERE tenant_id = :tenant_id ORDER BY name",
            ['tenant_id' => $context['tenant_id']]
        );
        
        json_response(['devices' => $devices]);
        break;
        
    default:
        json_response(['error' => 'Invalid action'], 400);
}

