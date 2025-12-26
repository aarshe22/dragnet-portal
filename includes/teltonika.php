<?php

/**
 * Teltonika Functions (Procedural)
 * Teltonika FMM13A device integration
 * 
 * Note: This assumes telemetry data is already decoded and normalized.
 * The actual Teltonika protocol parsing would be handled by a separate service.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Store telemetry data from Teltonika device
 * 
 * This function receives normalized telemetry data (already decoded from Codec8/8E)
 */
function teltonika_store_telemetry(int $deviceId, array $telemetryData): bool
{
    $sql = "INSERT INTO telemetry (
        device_id, timestamp, lat, lon, altitude, speed, heading, satellites, hdop,
        ignition, rpm, vehicle_speed, fuel_level, engine_load, odometer,
        gsm_signal, battery_voltage, external_voltage, internal_battery_level, temperature, io_payload
    ) VALUES (
        :device_id, :timestamp, :lat, :lon, :altitude, :speed, :heading, :satellites, :hdop,
        :ignition, :rpm, :vehicle_speed, :fuel_level, :engine_load, :odometer,
        :gsm_signal, :battery_voltage, :external_voltage, :internal_battery_level, :temperature, :io_payload
    )";
    
    // Prepare data with defaults
    $data = [
        'device_id' => $deviceId,
        'timestamp' => $telemetryData['timestamp'] ?? date('Y-m-d H:i:s'),
        'lat' => $telemetryData['lat'] ?? 0,
        'lon' => $telemetryData['lon'] ?? 0,
        'altitude' => $telemetryData['altitude'] ?? null,
        'speed' => $telemetryData['speed'] ?? null,
        'heading' => $telemetryData['heading'] ?? null,
        'satellites' => $telemetryData['satellites'] ?? null,
        'hdop' => $telemetryData['hdop'] ?? null,
        'ignition' => $telemetryData['ignition'] ?? false,
        'rpm' => $telemetryData['rpm'] ?? null,
        'vehicle_speed' => $telemetryData['vehicle_speed'] ?? null,
        'fuel_level' => $telemetryData['fuel_level'] ?? null,
        'engine_load' => $telemetryData['engine_load'] ?? null,
        'odometer' => $telemetryData['odometer'] ?? null,
        'gsm_signal' => $telemetryData['gsm_signal'] ?? null,
        'battery_voltage' => $telemetryData['battery_voltage'] ?? null,
        'external_voltage' => $telemetryData['external_voltage'] ?? null,
        'internal_battery_level' => $telemetryData['internal_battery_level'] ?? null,
        'temperature' => $telemetryData['temperature'] ?? null,
        'io_payload' => isset($telemetryData['io_payload']) ? json_encode($telemetryData['io_payload']) : null,
    ];
    
    try {
        db_execute($sql, $data);
        
        // Get the inserted telemetry ID
        $telemetryId = db_last_insert_id();
        $telemetryData['telemetry_id'] = $telemetryId;
        
        // Update device status
        teltonika_update_device_from_telemetry($deviceId, $telemetryData);
        
        // Detect and process trips
        require_once __DIR__ . '/trips.php';
        trip_detect_from_telemetry($deviceId, $telemetryData);
        
        return true;
    } catch (Exception $e) {
        error_log('Teltonika telemetry storage error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update device status from telemetry data
 */
function teltonika_update_device_from_telemetry(int $deviceId, array $telemetryData): void
{
    $updateData = [
        'last_seen' => $telemetryData['timestamp'] ?? date('Y-m-d H:i:s'),
    ];
    
    if (isset($telemetryData['gsm_signal'])) {
        $updateData['gsm_signal'] = $telemetryData['gsm_signal'];
    }
    
    if (isset($telemetryData['external_voltage'])) {
        $updateData['external_voltage'] = $telemetryData['external_voltage'];
    }
    
    if (isset($telemetryData['internal_battery_level'])) {
        $updateData['internal_battery'] = $telemetryData['internal_battery_level'];
    }
    
    // Determine status based on telemetry
    $speed = (float)($telemetryData['speed'] ?? 0);
    $ignition = (bool)($telemetryData['ignition'] ?? false);
    
    $status = 'online';
    if ($speed > 5) {
        $status = 'moving';
    } elseif ($ignition && $speed <= 0.5) {
        $status = 'idle';
    } elseif (!$ignition) {
        $status = 'parked';
    }
    
    $updateData['status'] = $status;
    
    db_execute(
        "UPDATE devices SET last_seen = :last_seen, gsm_signal = :gsm_signal, 
         external_voltage = :external_voltage, internal_battery = :internal_battery, status = :status 
         WHERE id = :device_id",
        array_merge($updateData, ['device_id' => $deviceId])
    );
}

/**
 * Get device IO element labels
 */
function teltonika_get_io_labels(int $deviceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT io_id, io_type, label FROM device_io_labels 
            WHERE tenant_id = :tenant_id AND (device_id = :device_id OR device_id IS NULL)
            ORDER BY io_type, io_id";
    
    $labels = db_fetch_all($sql, ['tenant_id' => $tenantId, 'device_id' => $deviceId]);
    
    $result = [];
    foreach ($labels as $label) {
        $result[$label['io_type']][$label['io_id']] = $label['label'];
    }
    
    return $result;
}

/**
 * Set device IO element label
 */
function teltonika_set_io_label(int $deviceId, int $ioId, string $ioType, string $label, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "INSERT INTO device_io_labels (tenant_id, device_id, io_id, io_type, label) 
            VALUES (:tenant_id, :device_id, :io_id, :io_type, :label)
            ON DUPLICATE KEY UPDATE label = :label";
    
    $affected = db_execute($sql, [
        'tenant_id' => $tenantId,
        'device_id' => $deviceId,
        'io_id' => $ioId,
        'io_type' => $ioType,
        'label' => $label
    ]);
    
    return $affected > 0;
}

