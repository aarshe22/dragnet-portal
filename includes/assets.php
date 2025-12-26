<?php

/**
 * Asset Functions (Procedural)
 * Asset management with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Find asset by ID (tenant-scoped)
 */
function asset_find(int $assetId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM assets WHERE id = :id AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['id' => $assetId, 'tenant_id' => $tenantId]);
}

/**
 * Find asset with device information (single device - deprecated, use asset_find_with_devices)
 */
function asset_find_with_device(int $assetId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT a.*, d.device_uid, d.imei, d.status as device_status, 
                   d.last_seen, d.gsm_signal, d.external_voltage, d.internal_battery
            FROM assets a
            LEFT JOIN devices d ON d.asset_id = a.id
            WHERE a.id = :id AND a.tenant_id = :tenant_id
            LIMIT 1";
    
    return db_fetch_one($sql, ['id' => $assetId, 'tenant_id' => $tenantId]);
}

/**
 * Find asset with all linked devices
 */
function asset_find_with_devices(int $assetId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Get asset
    $asset = asset_find($assetId, $tenantId);
    if (!$asset) {
        return null;
    }
    
    // Get all linked devices
    $devices = db_fetch_all(
        "SELECT d.*, 
                (SELECT COUNT(*) FROM telemetry WHERE device_id = d.id) as telemetry_count
         FROM devices d
         WHERE d.asset_id = :asset_id AND d.tenant_id = :tenant_id
         ORDER BY d.device_uid ASC",
        ['asset_id' => $assetId, 'tenant_id' => $tenantId]
    );
    
    $asset['devices'] = $devices;
    $asset['device_count'] = count($devices);
    
    return $asset;
}

/**
 * Get all assets for tenant with device counts
 */
function asset_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT a.*, 
                   COUNT(DISTINCT d.id) as device_count,
                   GROUP_CONCAT(DISTINCT d.device_uid ORDER BY d.device_uid SEPARATOR ', ') as device_uids
            FROM assets a
            LEFT JOIN devices d ON d.asset_id = a.id AND d.tenant_id = a.tenant_id
            WHERE a.tenant_id = :tenant_id
            GROUP BY a.id
            ORDER BY a.name ASC";
    
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Create asset
 */
function asset_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO assets (tenant_id, name, vehicle_id, status) 
            VALUES (:tenant_id, :name, :vehicle_id, :status)";
    
    $params = [
        'tenant_id' => $tenantId,
        'name' => $data['name'],
        'vehicle_id' => $data['vehicle_id'] ?? null,
        'status' => $data['status'] ?? 'active'
    ];
    
    db_execute($sql, $params);
    return (int)db_last_insert_id();
}

/**
 * Update asset
 */
function asset_update(int $assetId, array $data, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $fields = array_keys($data);
    $set = array_map(fn($f) => "{$f} = :{$f}", $fields);
    
    $sql = "UPDATE assets SET " . implode(', ', $set) . 
           " WHERE id = :id AND tenant_id = :tenant_id";
    
    $params = array_merge($data, ['id' => $assetId, 'tenant_id' => $tenantId]);
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete asset
 */
function asset_delete(int $assetId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $affected = db_execute(
        "DELETE FROM assets WHERE id = :id AND tenant_id = :tenant_id",
        ['id' => $assetId, 'tenant_id' => $tenantId]
    );
    return $affected > 0;
}

/**
 * Count assets
 */
function asset_count(?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $result = db_fetch_one(
        "SELECT COUNT(*) as count FROM assets WHERE tenant_id = :tenant_id",
        ['tenant_id' => $tenantId]
    );
    
    return (int)($result['count'] ?? 0);
}

/**
 * Get devices linked to an asset
 */
function asset_get_devices(int $assetId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify asset belongs to tenant
    $asset = asset_find($assetId, $tenantId);
    if (!$asset) {
        return [];
    }
    
    return db_fetch_all(
        "SELECT d.* FROM devices d
         WHERE d.asset_id = :asset_id AND d.tenant_id = :tenant_id
         ORDER BY d.device_uid ASC",
        ['asset_id' => $assetId, 'tenant_id' => $tenantId]
    );
}

/**
 * Get unlinked devices for a tenant (devices not assigned to any asset)
 */
function asset_get_unlinked_devices(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    return db_fetch_all(
        "SELECT d.* FROM devices d
         WHERE d.tenant_id = :tenant_id AND (d.asset_id IS NULL OR d.asset_id = 0)
         ORDER BY d.device_uid ASC",
        ['tenant_id' => $tenantId]
    );
}

