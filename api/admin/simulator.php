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
            'speed' => isset($_POST['speed']) && $_POST['speed'] !== '' ? (float)$_POST['speed'] : null,
            'moving' => isset($_POST['moving']) && $_POST['moving'] !== '' ? (bool)$_POST['moving'] : null,
            'route' => $_POST['route'] ?? 'random',
            'interval' => (int)($_POST['interval'] ?? 30),
        ];
        
        if (!$deviceId) {
            json_response(['error' => 'Device ID required'], 400);
        }
        
        try {
            $telemetry = teltonika_simulator_generate_telemetry($deviceId, $config);
            $device = db_fetch_one("SELECT imei FROM devices WHERE id = :id", ['id' => $deviceId]);
            
            if (!$device) {
                json_response(['error' => 'Device not found'], 404);
            }
            
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
            "SELECT id, device_uid, imei, status FROM devices WHERE tenant_id = :tenant_id ORDER BY device_uid",
            ['tenant_id' => $context['tenant_id']]
        );
        
        // Auto-create a simulated device if none exists
        if (empty($devices)) {
            require_once __DIR__ . '/../../includes/admin.php';
            
            // Generate a unique IMEI for the simulated device
            // IMEI format: 15 digits, starting with 86 (common for Teltonika)
            // Format: 86 + 7 random digits = 15 digits total
            $imei = '86' . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            
            // Ensure IMEI is exactly 15 digits
            if (strlen($imei) < 15) {
                $imei = str_pad($imei, 15, '0', STR_PAD_RIGHT);
            }
            
            // Check if IMEI already exists (unlikely but possible)
            $existing = db_fetch_one("SELECT id FROM devices WHERE imei = :imei", ['imei' => $imei]);
            $attempts = 0;
            while ($existing && $attempts < 10) {
                $imei = '86' . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
                if (strlen($imei) < 15) {
                    $imei = str_pad($imei, 15, '0', STR_PAD_RIGHT);
                }
                $existing = db_fetch_one("SELECT id FROM devices WHERE imei = :imei", ['imei' => $imei]);
                $attempts++;
            }
            
            // Generate device UID with SIM prefix
            $deviceUid = 'SIM-' . strtoupper(substr(md5($imei . time() . $context['tenant_id']), 0, 8));
            
            // Create the simulated device
            $deviceData = [
                'tenant_id' => $context['tenant_id'],
                'device_uid' => $deviceUid,
                'imei' => $imei,
                'model' => 'FMM13A',
                'device_type' => 'vehicle',
                'status' => 'offline'
            ];
            
            try {
                $deviceId = admin_create_device($deviceData);
                
                // Reload devices list
                $devices = db_fetch_all(
                    "SELECT id, device_uid, imei, status FROM devices WHERE tenant_id = :tenant_id ORDER BY device_uid",
                    ['tenant_id' => $context['tenant_id']]
                );
            } catch (Exception $e) {
                error_log('Failed to auto-create simulated device: ' . $e->getMessage());
            }
        }
        
        json_response(['devices' => $devices]);
        break;
        
    default:
        json_response(['error' => 'Invalid action'], 400);
}

