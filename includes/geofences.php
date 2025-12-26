<?php

/**
 * Geofences Functions (Procedural)
 * Geofence management with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get all geofences for tenant
 */
function geofence_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM geofences WHERE tenant_id = :tenant_id ORDER BY name ASC";
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

/**
 * Find geofence by ID (tenant-scoped)
 */
function geofence_find(int $geofenceId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM geofences WHERE id = :id AND tenant_id = :tenant_id";
    $geofence = db_fetch_one($sql, ['id' => $geofenceId, 'tenant_id' => $tenantId]);
    
    if ($geofence) {
        // Decode JSON fields
        $geofence['coordinates'] = json_decode($geofence['coordinates'], true);
        $geofence['rules'] = $geofence['rules'] ? json_decode($geofence['rules'], true) : null;
        $geofence['actions'] = $geofence['actions'] ? json_decode($geofence['actions'], true) : null;
    }
    
    return $geofence;
}

/**
 * Create a new geofence
 */
function geofence_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO geofences (tenant_id, name, type, coordinates, rules, actions, active) 
            VALUES (:tenant_id, :name, :type, :coordinates, :rules, :actions, :active)";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'name' => $data['name'],
        'type' => $data['type'],
        'coordinates' => json_encode($data['coordinates']),
        'rules' => isset($data['rules']) ? json_encode($data['rules']) : null,
        'actions' => isset($data['actions']) ? json_encode($data['actions']) : null,
        'active' => $data['active'] ?? 1
    ]);
    
    return (int)db_last_insert_id();
}

/**
 * Update geofence
 */
function geofence_update(int $geofenceId, array $data, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $fields = [];
    $params = ['id' => $geofenceId, 'tenant_id' => $tenantId];
    
    $allowedFields = ['name', 'type', 'coordinates', 'rules', 'actions', 'active'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if (in_array($field, ['coordinates', 'rules', 'actions'])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = is_string($data[$field]) ? $data[$field] : json_encode($data[$field]);
            } else {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE geofences SET " . implode(', ', $fields) . 
           " WHERE id = :id AND tenant_id = :tenant_id";
    
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete geofence
 */
function geofence_delete(int $geofenceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM geofences WHERE id = :id AND tenant_id = :tenant_id";
    $affected = db_execute($sql, ['id' => $geofenceId, 'tenant_id' => $tenantId]);
    return $affected > 0;
}

/**
 * Get devices associated with geofence
 */
function geofence_get_devices(int $geofenceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT d.* FROM devices d
            INNER JOIN geofence_devices gd ON d.id = gd.device_id
            WHERE gd.geofence_id = :geofence_id AND d.tenant_id = :tenant_id
            ORDER BY d.device_uid ASC";
    
    return db_fetch_all($sql, ['geofence_id' => $geofenceId, 'tenant_id' => $tenantId]);
}

/**
 * Get groups associated with geofence
 */
function geofence_get_groups(int $geofenceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT dg.* FROM device_groups dg
            INNER JOIN geofence_groups gg ON dg.id = gg.group_id
            WHERE gg.geofence_id = :geofence_id AND dg.tenant_id = :tenant_id
            ORDER BY dg.name ASC";
    
    return db_fetch_all($sql, ['geofence_id' => $geofenceId, 'tenant_id' => $tenantId]);
}

/**
 * Add device to geofence
 */
function geofence_add_device(int $geofenceId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify geofence belongs to tenant
    $geofence = geofence_find($geofenceId, $tenantId);
    if (!$geofence) {
        return false;
    }
    
    // Verify device belongs to tenant
    require_once __DIR__ . '/devices.php';
    $device = device_find($deviceId, $tenantId);
    if (!$device) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO geofence_devices (geofence_id, device_id) 
                VALUES (:geofence_id, :device_id)
                ON DUPLICATE KEY UPDATE geofence_id = geofence_id";
        db_execute($sql, ['geofence_id' => $geofenceId, 'device_id' => $deviceId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove device from geofence
 */
function geofence_remove_device(int $geofenceId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM geofence_devices 
            WHERE geofence_id = :geofence_id AND device_id = :device_id";
    $affected = db_execute($sql, ['geofence_id' => $geofenceId, 'device_id' => $deviceId]);
    return $affected > 0;
}

/**
 * Add group to geofence
 */
function geofence_add_group(int $geofenceId, int $groupId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify geofence belongs to tenant
    $geofence = geofence_find($geofenceId, $tenantId);
    if (!$geofence) {
        return false;
    }
    
    // Verify group belongs to tenant
    require_once __DIR__ . '/device_groups.php';
    $group = device_group_find($groupId, $tenantId);
    if (!$group) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO geofence_groups (geofence_id, group_id) 
                VALUES (:geofence_id, :group_id)
                ON DUPLICATE KEY UPDATE geofence_id = geofence_id";
        db_execute($sql, ['geofence_id' => $geofenceId, 'group_id' => $groupId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove group from geofence
 */
function geofence_remove_group(int $geofenceId, int $groupId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM geofence_groups 
            WHERE geofence_id = :geofence_id AND group_id = :group_id";
    $affected = db_execute($sql, ['geofence_id' => $geofenceId, 'group_id' => $groupId]);
    return $affected > 0;
}

/**
 * Check if point is inside geofence
 */
function geofence_contains_point(array $geofence, float $lat, float $lon): bool
{
    if (!$geofence['active']) {
        return false;
    }
    
    $coordinates = is_string($geofence['coordinates']) 
        ? json_decode($geofence['coordinates'], true) 
        : $geofence['coordinates'];
    
    if (!$coordinates) {
        return false;
    }
    
    switch ($geofence['type']) {
        case 'circle':
            // Circle: coordinates = [centerLat, centerLon, radiusKm]
            if (count($coordinates) >= 3) {
                $centerLat = $coordinates[0];
                $centerLon = $coordinates[1];
                $radiusKm = $coordinates[2];
                $distance = haversine_distance($lat, $lon, $centerLat, $centerLon);
                return $distance <= $radiusKm;
            }
            break;
            
        case 'rectangle':
            // Rectangle: coordinates = [[minLat, minLon], [maxLat, maxLon]]
            if (count($coordinates) >= 2) {
                $minLat = min($coordinates[0][0], $coordinates[1][0]);
                $maxLat = max($coordinates[0][0], $coordinates[1][0]);
                $minLon = min($coordinates[0][1], $coordinates[1][1]);
                $maxLon = max($coordinates[0][1], $coordinates[1][1]);
                return $lat >= $minLat && $lat <= $maxLat && $lon >= $minLon && $lon <= $maxLon;
            }
            break;
            
        case 'polygon':
            // Polygon: coordinates = [[lat, lon], [lat, lon], ...]
            if (count($coordinates) >= 3) {
                return point_in_polygon($lat, $lon, $coordinates);
            }
            break;
    }
    
    return false;
}

/**
 * Calculate distance between two points using Haversine formula
 */
function haversine_distance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

/**
 * Check if point is inside polygon using ray casting algorithm
 */
function point_in_polygon(float $lat, float $lon, array $polygon): bool
{
    $inside = false;
    $j = count($polygon) - 1;
    
    for ($i = 0; $i < count($polygon); $i++) {
        $xi = $polygon[$i][0];
        $yi = $polygon[$i][1];
        $xj = $polygon[$j][0];
        $yj = $polygon[$j][1];
        
        $intersect = (($yi > $lat) != ($yj > $lat)) &&
                     ($lon < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);
        
        if ($intersect) {
            $inside = !$inside;
        }
        
        $j = $i;
    }
    
    return $inside;
}

