<?php

/**
 * Device Groups Functions (Procedural)
 * Device group and membership management with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get all device groups for tenant
 */
function device_group_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM device_groups WHERE tenant_id = :tenant_id ORDER BY name ASC";
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Find device group by ID (tenant-scoped)
 */
function device_group_find(int $groupId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM device_groups WHERE id = :id AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['id' => $groupId, 'tenant_id' => $tenantId]);
}

/**
 * Create a new device group
 */
function device_group_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO device_groups (tenant_id, name, description, color, active) 
            VALUES (:tenant_id, :name, :description, :color, :active)";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'color' => $data['color'] ?? '#007bff',
        'active' => $data['active'] ?? 1
    ]);
    
    return (int)db_last_insert_id();
}

/**
 * Update device group
 */
function device_group_update(int $groupId, array $data, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $fields = [];
    $params = ['id' => $groupId, 'tenant_id' => $tenantId];
    
    $allowedFields = ['name', 'description', 'color', 'active'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "{$field} = :{$field}";
            $params[$field] = $data[$field];
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE device_groups SET " . implode(', ', $fields) . 
           " WHERE id = :id AND tenant_id = :tenant_id";
    
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete device group
 */
function device_group_delete(int $groupId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM device_groups WHERE id = :id AND tenant_id = :tenant_id";
    $affected = db_execute($sql, ['id' => $groupId, 'tenant_id' => $tenantId]);
    return $affected > 0;
}

/**
 * Get devices in a group
 */
function device_group_get_devices(int $groupId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT d.* FROM devices d
            INNER JOIN device_group_members dgm ON d.id = dgm.device_id
            WHERE dgm.group_id = :group_id AND d.tenant_id = :tenant_id
            ORDER BY d.device_uid ASC";
    
    return db_fetch_all($sql, ['group_id' => $groupId, 'tenant_id' => $tenantId]);
}

/**
 * Add device to group
 */
function device_group_add_device(int $groupId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify group belongs to tenant
    $group = device_group_find($groupId, $tenantId);
    if (!$group) {
        return false;
    }
    
    // Verify device belongs to tenant
    require_once __DIR__ . '/devices.php';
    $device = device_find($deviceId, $tenantId);
    if (!$device) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO device_group_members (group_id, device_id) 
                VALUES (:group_id, :device_id)
                ON DUPLICATE KEY UPDATE group_id = group_id";
        db_execute($sql, ['group_id' => $groupId, 'device_id' => $deviceId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove device from group
 */
function device_group_remove_device(int $groupId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify group belongs to tenant
    $group = device_group_find($groupId, $tenantId);
    if (!$group) {
        return false;
    }
    
    $sql = "DELETE FROM device_group_members 
            WHERE group_id = :group_id AND device_id = :device_id";
    $affected = db_execute($sql, ['group_id' => $groupId, 'device_id' => $deviceId]);
    return $affected > 0;
}

/**
 * Get groups for a device
 */
function device_get_groups(int $deviceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT dg.* FROM device_groups dg
            INNER JOIN device_group_members dgm ON dg.id = dgm.group_id
            WHERE dgm.device_id = :device_id AND dg.tenant_id = :tenant_id
            ORDER BY dg.name ASC";
    
    return db_fetch_all($sql, ['device_id' => $deviceId, 'tenant_id' => $tenantId]);
}

