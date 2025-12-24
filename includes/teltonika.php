<?php

/**
 * Teltonika Controller Functions
 */

require_once __DIR__ . '/../src/Services/TeltonikaProtocolParser.php';

function teltonika_receive(): void
{
    // Get raw binary data
    $data = file_get_contents('php://input');
    
    if (empty($data)) {
        http_response_code(400);
        echo 'No data received';
        exit;
    }
    
    try {
        // Parse the packet
        $parsed = \DragNet\Services\TeltonikaProtocolParser::parsePacket($data);
        
        // Handle IMEI registration
        if ($parsed['imei']) {
            teltonika_handle_imei($parsed['imei']);
            echo '01'; // Acknowledge IMEI
            exit;
        }
        
        // Handle AVL data
        if (!empty($parsed['records'])) {
            $device = teltonika_identify_device($parsed);
            
            if (!$device) {
                $imei = $_SERVER['HTTP_X_IMEI'] ?? $_GET['imei'] ?? 'unknown';
                error_log("Teltonika: Unregistered device attempted to send data. IMEI: {$imei}");
                
                $ack = \DragNet\Services\TeltonikaProtocolParser::generateAck(0);
                header('Content-Type: application/octet-stream');
                echo $ack;
                exit;
            }
            
            $processed = teltonika_process_avl_records($device, $parsed['records']);
            
            $ack = \DragNet\Services\TeltonikaProtocolParser::generateAck($processed);
            header('Content-Type: application/octet-stream');
            echo $ack;
            exit;
        }
        
        http_response_code(400);
        echo 'Invalid packet format';
        
    } catch (\Exception $e) {
        error_log('Teltonika parsing error: ' . $e->getMessage());
        http_response_code(500);
        echo 'Processing error';
    }
}

function teltonika_handle_imei(string $imei): void
{
    $device = device_find_by_imei($imei);
    
    if (!$device) {
        error_log("Unregistered Teltonika device IMEI: {$imei}");
    }
}

function teltonika_identify_device(array $parsed): ?array
{
    $imei = $_SERVER['HTTP_X_IMEI'] ?? null;
    
    if (!$imei) {
        $imei = $_GET['imei'] ?? null;
    }
    
    if (!$imei && !empty($parsed['records'])) {
        $firstRecord = $parsed['records'][0];
        $imei = $firstRecord['io_elements']['imei'] ?? null;
    }
    
    if (!$imei) {
        error_log('Cannot identify device: IMEI not provided');
        return null;
    }
    
    return device_find_by_imei($imei);
}

function teltonika_process_avl_records(array $device, array $records): int
{
    $processed = 0;
    
    foreach ($records as $record) {
        try {
            $gps = $record['gps'];
            $io = $record['io_elements'];
            
            $telemetryData = [
                'device_id' => $device['id'],
                'timestamp' => $record['timestamp'],
                'lat' => $gps['latitude'],
                'lon' => $gps['longitude'],
                'speed' => $gps['speed'] ?? null,
                'heading' => $gps['angle'] ?? null,
                'altitude' => $gps['altitude'] ?? null,
                'satellites' => $gps['satellites'] ?? null,
                'priority' => $record['priority'] ?? null,
                'odometer' => $io['odometer'] ?? null,
                'io_elements' => !empty($io) ? json_encode($io) : null,
            ];
            
            db_execute(
                "INSERT INTO telemetry (device_id, timestamp, lat, lon, speed, heading, altitude, satellites, priority, odometer, io_elements) 
                 VALUES (:device_id, :timestamp, :lat, :lon, :speed, :heading, :altitude, :satellites, :priority, :odometer, :io_elements)",
                $telemetryData
            );
            
            $updateData = [
                'last_checkin' => $record['timestamp'],
                'status' => 'online',
            ];
            
            if (isset($io['battery_level'])) {
                $updateData['battery_level'] = $io['battery_level'];
            }
            if (isset($io['signal_strength'])) {
                $updateData['signal_strength'] = $io['signal_strength'];
            }
            
            model_update('devices', $device['id'], $updateData, $device['tenant_id']);
            
            $processed++;
            
        } catch (\Exception $e) {
            error_log('Error processing AVL record: ' . $e->getMessage());
        }
    }
    
    return $processed;
}

