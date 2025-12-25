<?php

/**
 * Teltonika Telemetry Simulator
 * Generates realistic test data and streams it like a real device
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/devices.php';

/**
 * Generate realistic telemetry data for a device
 */
function teltonika_simulator_generate_telemetry(int $deviceId, array $config = []): array
{
    $device = db_fetch_one("SELECT * FROM devices WHERE id = :id", ['id' => $deviceId]);
    
    if (!$device) {
        throw new Exception("Device not found");
    }
    
    // Get last telemetry to continue from there
    $lastTelemetry = db_fetch_one(
        "SELECT * FROM telemetry WHERE device_id = :device_id ORDER BY timestamp DESC LIMIT 1",
        ['device_id' => $deviceId]
    );
    
    // Default configuration
    $defaults = [
        'start_lat' => $lastTelemetry['lat'] ?? ($config['start_lat'] ?? 40.7128),
        'start_lon' => $lastTelemetry['lon'] ?? ($config['start_lon'] ?? -74.0060),
        'speed' => $config['speed'] ?? null, // null = random
        'moving' => $config['moving'] ?? null, // null = random
        'route' => $config['route'] ?? 'random', // 'random', 'circle', 'line'
        'interval' => $config['interval'] ?? 30, // seconds between updates
    ];
    
    $config = array_merge($defaults, $config);
    
    // Calculate new position based on route type
    $position = teltonika_simulator_calculate_position(
        $lastTelemetry ? ['lat' => $lastTelemetry['lat'], 'lon' => $lastTelemetry['lon']] : ['lat' => $config['start_lat'], 'lon' => $config['start_lon']],
        $config
    );
    
    // Determine if moving
    $isMoving = $config['moving'] ?? (rand(0, 100) > 30); // 70% chance of moving
    $currentSpeed = $config['speed'] ?? ($isMoving ? rand(30, 100) : rand(0, 5));
    
    // Calculate heading based on movement
    $heading = $position['heading'] ?? rand(0, 360);
    
    // Determine ignition state
    $ignition = $isMoving || rand(0, 100) > 20; // 80% chance if moving
    
    // Generate realistic telemetry data
    $telemetry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'lat' => $position['lat'],
        'lon' => $position['lon'],
        'altitude' => rand(0, 500),
        'speed' => $currentSpeed,
        'heading' => $heading,
        'satellites' => rand(6, 12),
        'hdop' => round(rand(10, 50) / 10, 1),
        'ignition' => $ignition,
        'rpm' => $isMoving ? rand(1500, 3000) : ($ignition ? rand(800, 1200) : 0),
        'vehicle_speed' => $currentSpeed,
        'fuel_level' => rand(20, 100), // percentage
        'engine_load' => $isMoving ? rand(40, 90) : rand(0, 20),
        'odometer' => ($lastTelemetry['odometer'] ?? 0) + ($currentSpeed * $config['interval'] / 3600), // km
        'gsm_signal' => rand(60, 100), // percentage
        'battery_voltage' => round(rand(120, 140) / 10, 1), // 12.0-14.0V
        'external_voltage' => round(rand(120, 140) / 10, 1),
        'internal_battery_level' => rand(80, 100), // percentage
        'temperature' => round(rand(150, 250) / 10, 1), // 15-25°C
        'io_payload' => [
            'digital_input_1' => rand(0, 1),
            'digital_input_2' => rand(0, 1),
            'analog_input_1' => rand(0, 4095),
            'analog_input_2' => rand(0, 4095),
            'acceleration_x' => round(rand(-100, 100) / 100, 2),
            'acceleration_y' => round(rand(-100, 100) / 100, 2),
            'acceleration_z' => round(rand(900, 1100) / 100, 2), // gravity
        ]
    ];
    
    return $telemetry;
}

/**
 * Calculate next position based on route type
 */
function teltonika_simulator_calculate_position(array $currentPos, array $config): array
{
    $lat = $currentPos['lat'];
    $lon = $currentPos['lon'];
    $speed = $config['speed'] ?? rand(30, 80); // km/h
    $interval = $config['interval'] ?? 30; // seconds
    
    // Convert speed to degrees (rough approximation)
    // 1 degree latitude ≈ 111 km
    // 1 degree longitude ≈ 111 km * cos(latitude)
    $kmPerSecond = ($speed / 3600);
    $distanceKm = $kmPerSecond * $interval;
    $latDelta = $distanceKm / 111;
    $lonDelta = $distanceKm / (111 * cos(deg2rad($lat)));
    
    switch ($config['route']) {
        case 'circle':
            // Move in a circle
            static $angle = 0;
            $angle += ($speed * $interval / 1000) * 10; // degrees
            if ($angle >= 360) $angle = 0;
            
            $radius = 0.01; // ~1km radius
            $lat = $config['start_lat'] + $radius * cos(deg2rad($angle));
            $lon = $config['start_lon'] + $radius * sin(deg2rad($angle));
            $heading = ($angle + 90) % 360;
            break;
            
        case 'line':
            // Move in a straight line
            $heading = $config['heading'] ?? 45; // degrees
            $lat += $latDelta * cos(deg2rad($heading));
            $lon += $lonDelta * sin(deg2rad($heading));
            break;
            
        case 'random':
        default:
            // Random movement
            $heading = rand(0, 360);
            $lat += $latDelta * cos(deg2rad($heading)) * (rand(50, 150) / 100);
            $lon += $lonDelta * sin(deg2rad($heading)) * (rand(50, 150) / 100);
            break;
    }
    
    return [
        'lat' => round($lat, 6),
        'lon' => round($lon, 6),
        'heading' => round($heading)
    ];
}

/**
 * Send telemetry data to the endpoint
 */
function teltonika_simulator_send_telemetry(string $imei, array $telemetryData): bool
{
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
        . '://' . $_SERVER['HTTP_HOST'] 
        . '/api/teltonika/telemetry.php?imei=' . urlencode($imei);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($telemetryData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-IMEI: ' . $imei
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

/**
 * Run simulator for a device
 */
function teltonika_simulator_run(int $deviceId, array $config = [], int $iterations = null): array
{
    $device = db_fetch_one("SELECT * FROM devices WHERE id = :id", ['id' => $deviceId]);
    
    if (!$device) {
        throw new Exception("Device not found");
    }
    
    $results = [
        'success' => 0,
        'failed' => 0,
        'total' => 0
    ];
    
    $interval = $config['interval'] ?? 30;
    $count = 0;
    
    while ($iterations === null || $count < $iterations) {
        try {
            $telemetry = teltonika_simulator_generate_telemetry($deviceId, $config);
            
            if (teltonika_simulator_send_telemetry($device['imei'], $telemetry)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $results['total']++;
            $count++;
            
            // Update config with new position for next iteration
            $config['start_lat'] = $telemetry['lat'];
            $config['start_lon'] = $telemetry['lon'];
            
            if ($iterations === null || $count < $iterations) {
                sleep($interval);
            }
        } catch (Exception $e) {
            error_log('Simulator error: ' . $e->getMessage());
            $results['failed']++;
            $results['total']++;
            break;
        }
    }
    
    return $results;
}

