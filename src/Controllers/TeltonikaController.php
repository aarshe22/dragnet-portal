<?php

namespace DragNet\Controllers;

use DragNet\Services\TeltonikaProtocolParser;
use DragNet\Models\Device;
use DragNet\Models\Telemetry;

/**
 * Teltonika Device Data Ingestion Controller
 * 
 * Receives and processes data from Teltonika FMM13A and other devices
 */
class TeltonikaController extends BaseController
{
    /**
     * Receive Teltonika data (HTTP endpoint)
     * 
     * This endpoint accepts binary data from Teltonika devices.
     * Devices can be configured to send data via HTTP POST.
     */
    public function receive(): void
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
            $parsed = TeltonikaProtocolParser::parsePacket($data);
            
            // Handle IMEI registration
            if ($parsed['imei']) {
                $this->handleImeiRegistration($parsed['imei']);
                echo '01'; // Acknowledge IMEI
                exit;
            }
            
            // Handle AVL data
            if (!empty($parsed['records'])) {
                $device = $this->identifyDevice($parsed);
                
                if (!$device) {
                    // Log unregistered device attempt
                    $imei = $_SERVER['HTTP_X_IMEI'] ?? $_GET['imei'] ?? 'unknown';
                    error_log("Teltonika: Unregistered device attempted to send data. IMEI: {$imei}");
                    
                    // Still send acknowledgment to prevent device retry spam
                    $ack = TeltonikaProtocolParser::generateAck(0);
                    header('Content-Type: application/octet-stream');
                    echo $ack;
                    exit;
                }
                
                $processed = $this->processAvlRecords($device, $parsed['records']);
                
                // Send acknowledgment
                $ack = TeltonikaProtocolParser::generateAck($processed);
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
    
    /**
     * Handle IMEI registration (first connection from device)
     */
    private function handleImeiRegistration(string $imei): void
    {
        // Check if device exists
        $db = $this->app->getDatabase();
        $device = $db->fetchOne(
            "SELECT id, tenant_id FROM devices WHERE imei = :imei",
            ['imei' => $imei]
        );
        
        if (!$device) {
            // Device not registered - log for admin to register
            error_log("Unregistered Teltonika device IMEI: {$imei}");
        }
    }
    
    /**
     * Identify device from parsed data
     * 
     * For HTTP endpoints, we need to identify device by IMEI from session/header
     * or from the first record's metadata
     */
    private function identifyDevice(array $parsed): ?array
    {
        $db = $this->app->getDatabase();
        
        // Try to get IMEI from custom header (if device sends it)
        $imei = $_SERVER['HTTP_X_IMEI'] ?? null;
        
        // Or try to get from query parameter
        if (!$imei) {
            $imei = $_GET['imei'] ?? null;
        }
        
        // Or try to get from first record's IO elements (some devices send it)
        if (!$imei && !empty($parsed['records'])) {
            $firstRecord = $parsed['records'][0];
            $imei = $firstRecord['io_elements']['imei'] ?? null;
        }
        
        if (!$imei) {
            error_log('Cannot identify device: IMEI not provided');
            return null;
        }
        
        $device = $db->fetchOne(
            "SELECT * FROM devices WHERE imei = :imei",
            ['imei' => $imei]
        );
        
        return $device;
    }
    
    /**
     * Process AVL records and store telemetry
     */
    private function processAvlRecords(array $device, array $records): int
    {
        $deviceModel = new Device($this->app);
        $deviceModel->setTenantId($device['tenant_id']);
        
        $processed = 0;
        $db = $this->app->getDatabase();
        
        foreach ($records as $record) {
            try {
                // Extract GPS data
                $gps = $record['gps'];
                $io = $record['io_elements'];
                
                // Prepare telemetry data
                $telemetryData = [
                    'device_id' => $device['id'],
                    'timestamp' => $record['timestamp'],
                    'lat' => $gps['latitude'],
                    'lon' => $gps['longitude'],
                    'speed' => $gps['speed'] ?? null, // km/h
                    'heading' => $gps['angle'] ?? null,
                    'altitude' => $gps['altitude'] ?? null,
                    'satellites' => $gps['satellites'] ?? null,
                    'priority' => $record['priority'] ?? null,
                    'odometer' => $io['odometer'] ?? null,
                    'io_elements' => !empty($io) ? json_encode($io) : null,
                ];
                
                // Store telemetry
                $db->execute(
                    "INSERT INTO telemetry (device_id, timestamp, lat, lon, speed, heading, altitude, satellites, priority, odometer, io_elements) 
                     VALUES (:device_id, :timestamp, :lat, :lon, :speed, :heading, :altitude, :satellites, :priority, :odometer, :io_elements)",
                    $telemetryData
                );
                
                // Update device status
                $updateData = [
                    'last_checkin' => $record['timestamp'],
                    'status' => 'online',
                ];
                
                // Update battery and signal if available
                if (isset($io['battery_level'])) {
                    $updateData['battery_level'] = $io['battery_level'];
                }
                if (isset($io['signal_strength'])) {
                    $updateData['signal_strength'] = $io['signal_strength'];
                }
                
                $deviceModel->update($device['id'], $updateData);
                
                $processed++;
                
            } catch (\Exception $e) {
                error_log('Error processing AVL record: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
        }
        
        return $processed;
    }
    
    /**
     * TCP endpoint for direct device connections
     * 
     * Note: This requires a separate TCP server process or socket handling
     * For now, HTTP endpoint is recommended for easier deployment
     */
    public function tcpEndpoint(): void
    {
        // TCP handling would go here
        // Requires socket server implementation
        echo 'TCP endpoint - use HTTP endpoint for easier deployment';
    }
}

