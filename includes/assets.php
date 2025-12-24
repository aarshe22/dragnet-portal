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
 * Find asset with device information
 */
function asset_find_with_device(int $assetId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT a.*, d.device_uid, d.imei, d.status as device_status, 
                   d.last_seen, d.gsm_signal, d.external_voltage, d.internal_battery
            FROM assets a
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.id = :id AND a.tenant_id = :tenant_id";
    
    return db_fetch_one($sql, ['id' => $assetId, 'tenant_id' => $tenantId]);
}

/**
 * Get all assets for tenant
 */
function asset_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT a.*, d.device_uid, d.status as device_status, d.last_seen
            FROM assets a
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.tenant_id = :tenant_id
            ORDER BY a.name ASC";
    
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Create asset
 */
function asset_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO assets (tenant_id, name, vehicle_id, device_id, status) 
            VALUES (:tenant_id, :name, :vehicle_id, :device_id, :status)";
    
    db_execute($sql, array_merge($data, ['tenant_id' => $tenantId]));
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

