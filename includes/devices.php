<?php

/**
 * Device Functions (Procedural)
 * Device and telemetry operations with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Find device by ID (tenant-scoped)
 */
function device_find(int $deviceId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM devices WHERE id = :id AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['id' => $deviceId, 'tenant_id' => $tenantId]);
}

/**
 * Find device by IMEI (tenant-scoped)
 */
function device_find_by_imei(string $imei, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM devices WHERE imei = :imei AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['imei' => $imei, 'tenant_id' => $tenantId]);
}

/**
 * Get all devices for tenant
 */
function device_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM devices WHERE tenant_id = :tenant_id ORDER BY device_uid ASC";
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Get devices with latest telemetry status
 */
function device_list_with_status(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT d.*, 
                   (SELECT lat FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lat,
                   (SELECT lon FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lon,
                   (SELECT speed FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as speed,
                   (SELECT timestamp FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as last_position_time
            FROM devices d
            WHERE d.tenant_id = :tenant_id
            ORDER BY d.last_seen DESC";
    
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Get latest telemetry for device
 */
function device_get_latest_telemetry(int $deviceId): ?array
{
    $sql = "SELECT * FROM telemetry 
            WHERE device_id = :device_id 
            ORDER BY timestamp DESC 
            LIMIT 1";
    
    return db_fetch_one($sql, ['device_id' => $deviceId]);
}

/**
 * Get telemetry for device within time range
 */
function device_get_telemetry_range(int $deviceId, string $startTime, string $endTime): array
{
    $sql = "SELECT * FROM telemetry 
            WHERE device_id = :device_id 
            AND timestamp BETWEEN :start_time AND :end_time
            ORDER BY timestamp ASC";
    
    return db_fetch_all($sql, [
        'device_id' => $deviceId,
        'start_time' => $startTime,
        'end_time' => $endTime
    ]);
}

/**
 * Update device status based on last check-in and telemetry
 */
function device_update_status(int $deviceId, ?int $tenantId = null): void
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $device = device_find($deviceId, $tenantId);
    if (!$device) {
        return;
    }
    
    $lastSeen = strtotime($device['last_seen'] ?? '1970-01-01');
    $now = time();
    $minutesSinceSeen = ($now - $lastSeen) / 60;
    
    // Get latest telemetry
    $telemetry = device_get_latest_telemetry($deviceId);
    
    $status = 'offline';
    if ($minutesSinceSeen <= 15) {
        $status = 'online';
        
        if ($telemetry) {
            $speed = (float)($telemetry['speed'] ?? 0);
            $ignition = (bool)($telemetry['ignition'] ?? false);
            
            if ($speed > 5) {
                $status = 'moving';
            } elseif ($ignition && $speed <= 0.5) {
                $status = 'idle';
            } elseif (!$ignition) {
                $status = 'parked';
            }
        }
    }
    
    db_execute(
        "UPDATE devices SET status = :status WHERE id = :id AND tenant_id = :tenant_id",
        ['status' => $status, 'id' => $deviceId, 'tenant_id' => $tenantId]
    );
}

/**
 * Create device
 */
function device_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO devices (tenant_id, device_uid, imei, iccid, model, device_type, firmware_version, asset_id) 
            VALUES (:tenant_id, :device_uid, :imei, :iccid, :model, :device_type, :firmware_version, :asset_id)";
    
    db_execute($sql, array_merge($data, ['tenant_id' => $tenantId]));
    return (int)db_last_insert_id();
}

/**
 * Update device
 */
function device_update(int $deviceId, array $data, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $fields = array_keys($data);
    $set = array_map(fn($f) => "{$f} = :{$f}", $fields);
    
    $sql = "UPDATE devices SET " . implode(', ', $set) . 
           " WHERE id = :id AND tenant_id = :tenant_id";
    
    $params = array_merge($data, ['id' => $deviceId, 'tenant_id' => $tenantId]);
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Count devices by status
 */
function device_count_by_status(string $status, ?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $result = db_fetch_one(
        "SELECT COUNT(*) as count FROM devices WHERE tenant_id = :tenant_id AND status = :status",
        ['tenant_id' => $tenantId, 'status' => $status]
    );
    
    return (int)($result['count'] ?? 0);
}

/**
 * Count all devices for tenant (regardless of status)
 */
function device_count_all(?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $result = db_fetch_one(
        "SELECT COUNT(*) as count FROM devices WHERE tenant_id = :tenant_id",
        ['tenant_id' => $tenantId]
    );
    
    return (int)($result['count'] ?? 0);
}

