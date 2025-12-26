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
 * Get assets associated with geofence
 */
function geofence_get_assets(int $geofenceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT a.* FROM assets a
            INNER JOIN geofence_assets ga ON a.id = ga.asset_id
            WHERE ga.geofence_id = :geofence_id AND a.tenant_id = :tenant_id
            ORDER BY a.name ASC";
    
    return db_fetch_all($sql, ['geofence_id' => $geofenceId, 'tenant_id' => $tenantId]);
}

/**
 * Add asset to geofence
 */
function geofence_add_asset(int $geofenceId, int $assetId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify geofence belongs to tenant
    $geofence = geofence_find($geofenceId, $tenantId);
    if (!$geofence) {
        return false;
    }
    
    // Verify asset belongs to tenant
    require_once __DIR__ . '/assets.php';
    $asset = asset_find($assetId, $tenantId);
    if (!$asset) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO geofence_assets (geofence_id, asset_id) 
                VALUES (:geofence_id, :asset_id)
                ON DUPLICATE KEY UPDATE geofence_id = geofence_id";
        db_execute($sql, ['geofence_id' => $geofenceId, 'asset_id' => $assetId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove asset from geofence
 */
function geofence_remove_asset(int $geofenceId, int $assetId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM geofence_assets 
            WHERE geofence_id = :geofence_id AND asset_id = :asset_id";
    $affected = db_execute($sql, ['geofence_id' => $geofenceId, 'asset_id' => $assetId]);
    return $affected > 0;
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

/**
 * Check device position against all geofences and detect entry/exit events
 * Called when new telemetry is received
 */
function geofence_check_device_position(int $deviceId, array $telemetryData): void
{
    $device = db_fetch_one("SELECT tenant_id FROM devices WHERE id = :id", ['id' => $deviceId]);
    if (!$device) {
        return;
    }
    
    $tenantId = $device['tenant_id'];
    $lat = $telemetryData['lat'] ?? null;
    $lon = $telemetryData['lon'] ?? null;
    $timestamp = $telemetryData['timestamp'] ?? date('Y-m-d H:i:s');
    $telemetryId = $telemetryData['telemetry_id'] ?? null;
    
    if ($lat === null || $lon === null) {
        return;
    }
    
    // Get all active geofences for tenant
    $geofences = geofence_list_all($tenantId);
    
    foreach ($geofences as $geofence) {
        if (!$geofence['active']) {
            continue;
        }
        
        // Check if device is associated with this geofence (directly or via group)
        if (!geofence_is_device_monitored($geofence['id'], $deviceId, $tenantId)) {
            continue;
        }
        
        // Check if point is inside geofence
        $isInside = geofence_contains_point($geofence, $lat, $lon);
        
        // Get current state
        $state = geofence_get_device_state($deviceId, $geofence['id'], $tenantId);
        
        if ($state === null) {
            // First time checking - initialize state
            geofence_set_device_state($deviceId, $geofence['id'], $tenantId, $isInside, $timestamp, $telemetryId);
            
            if ($isInside) {
                // Device is inside on first check - create entry event
                geofence_create_event($geofence['id'], $deviceId, $tenantId, 'entry', $lat, $lon, $telemetryData, $telemetryId);
                geofence_create_alert($geofence['id'], $deviceId, $tenantId, 'geofence_entry', $lat, $lon, $telemetryData);
            }
        } else {
            $wasInside = (bool)$state['is_inside'];
            
            if ($isInside && !$wasInside) {
                // Entry event
                geofence_create_event($geofence['id'], $deviceId, $tenantId, 'entry', $lat, $lon, $telemetryData, $telemetryId);
                geofence_create_alert($geofence['id'], $deviceId, $tenantId, 'geofence_entry', $lat, $lon, $telemetryData);
                geofence_set_device_state($deviceId, $geofence['id'], $tenantId, true, $timestamp, $telemetryId);
            } elseif (!$isInside && $wasInside) {
                // Exit event
                geofence_create_event($geofence['id'], $deviceId, $tenantId, 'exit', $lat, $lon, $telemetryData, $telemetryId);
                geofence_create_alert($geofence['id'], $deviceId, $tenantId, 'geofence_exit', $lat, $lon, $telemetryData);
                geofence_set_device_state($deviceId, $geofence['id'], $tenantId, false, null, $telemetryId);
            } elseif ($isInside && $wasInside) {
                // Still inside - update last seen
                geofence_update_device_state($deviceId, $geofence['id'], $tenantId, $timestamp, $telemetryId);
            }
        }
    }
}

/**
 * Check if device is monitored by geofence (directly or via group)
 */
function geofence_is_device_monitored(int $geofenceId, int $deviceId, int $tenantId): bool
{
    // Check direct device association
    $direct = db_fetch_one(
        "SELECT 1 FROM geofence_devices gd
         INNER JOIN devices d ON gd.device_id = d.id
         WHERE gd.geofence_id = :geofence_id AND gd.device_id = :device_id AND d.tenant_id = :tenant_id",
        ['geofence_id' => $geofenceId, 'device_id' => $deviceId, 'tenant_id' => $tenantId]
    );
    
    if ($direct) {
        return true;
    }
    
    // Check group association
    require_once __DIR__ . '/device_groups.php';
    $groups = geofence_get_groups($geofenceId, $tenantId);
    
    foreach ($groups as $group) {
        $deviceInGroup = db_fetch_one(
            "SELECT 1 FROM device_group_members dgm
             INNER JOIN devices d ON dgm.device_id = d.id
             WHERE dgm.group_id = :group_id AND dgm.device_id = :device_id AND d.tenant_id = :tenant_id",
            ['group_id' => $group['id'], 'device_id' => $deviceId, 'tenant_id' => $tenantId]
        );
        
        if ($deviceInGroup) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get device geofence state
 */
function geofence_get_device_state(int $deviceId, int $geofenceId, int $tenantId): ?array
{
    return db_fetch_one(
        "SELECT * FROM device_geofence_state 
         WHERE device_id = :device_id AND geofence_id = :geofence_id AND tenant_id = :tenant_id",
        ['device_id' => $deviceId, 'geofence_id' => $geofenceId, 'tenant_id' => $tenantId]
    );
}

/**
 * Set device geofence state
 */
function geofence_set_device_state(int $deviceId, int $geofenceId, int $tenantId, bool $isInside, ?string $entryTime, ?int $telemetryId): void
{
    $sql = "INSERT INTO device_geofence_state 
           (tenant_id, device_id, geofence_id, is_inside, entry_time, last_seen_inside, last_telemetry_id)
           VALUES 
           (:tenant_id, :device_id, :geofence_id, :is_inside, :entry_time, :last_seen_inside, :last_telemetry_id)
           ON DUPLICATE KEY UPDATE
           is_inside = :is_inside,
           entry_time = :entry_time,
           last_seen_inside = :last_seen_inside,
           last_telemetry_id = :last_telemetry_id,
           updated_at = NOW()";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'device_id' => $deviceId,
        'geofence_id' => $geofenceId,
        'is_inside' => $isInside ? 1 : 0,
        'entry_time' => $isInside ? $entryTime : null,
        'last_seen_inside' => $isInside ? $entryTime : null,
        'last_telemetry_id' => $telemetryId
    ]);
}

/**
 * Update device geofence state (when still inside)
 */
function geofence_update_device_state(int $deviceId, int $geofenceId, int $tenantId, string $timestamp, ?int $telemetryId): void
{
    db_execute(
        "UPDATE device_geofence_state 
         SET last_seen_inside = :timestamp, last_telemetry_id = :telemetry_id, updated_at = NOW()
         WHERE device_id = :device_id AND geofence_id = :geofence_id AND tenant_id = :tenant_id",
        [
            'device_id' => $deviceId,
            'geofence_id' => $geofenceId,
            'tenant_id' => $tenantId,
            'timestamp' => $timestamp,
            'telemetry_id' => $telemetryId
        ]
    );
}

/**
 * Create geofence event record
 */
function geofence_create_event(int $geofenceId, int $deviceId, int $tenantId, string $eventType, float $lat, float $lon, array $telemetryData, ?int $telemetryId): int
{
    $sql = "INSERT INTO geofence_events 
           (tenant_id, geofence_id, device_id, event_type, timestamp, lat, lon, speed, heading, telemetry_id)
           VALUES 
           (:tenant_id, :geofence_id, :device_id, :event_type, :timestamp, :lat, :lon, :speed, :heading, :telemetry_id)";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'geofence_id' => $geofenceId,
        'device_id' => $deviceId,
        'event_type' => $eventType,
        'timestamp' => $telemetryData['timestamp'] ?? date('Y-m-d H:i:s'),
        'lat' => $lat,
        'lon' => $lon,
        'speed' => $telemetryData['speed'] ?? null,
        'heading' => $telemetryData['heading'] ?? null,
        'telemetry_id' => $telemetryId
    ]);
    
    return (int)db_last_insert_id();
}

/**
 * Create geofence alert
 */
function geofence_create_alert(int $geofenceId, int $deviceId, int $tenantId, string $alertType, float $lat, float $lon, array $telemetryData): void
{
    require_once __DIR__ . '/alerts.php';
    
    $geofence = geofence_find($geofenceId, $tenantId);
    if (!$geofence) {
        return;
    }
    
    $device = db_fetch_one("SELECT device_uid FROM devices WHERE id = :id", ['id' => $deviceId]);
    if (!$device) {
        return;
    }
    
    $message = sprintf(
        'Device %s %s geofence "%s"',
        $device['device_uid'],
        $alertType === 'geofence_entry' ? 'entered' : 'exited',
        $geofence['name']
    );
    
    $severity = $alertType === 'geofence_entry' ? 'info' : 'warning';
    
    $metadata = [
        'geofence_id' => $geofenceId,
        'geofence_name' => $geofence['name'],
        'lat' => $lat,
        'lon' => $lon,
        'speed' => $telemetryData['speed'] ?? null
    ];
    
    alert_create([
        'device_id' => $deviceId,
        'type' => $alertType,
        'severity' => $severity,
        'message' => $message,
        'metadata' => $metadata
    ], $tenantId);
}

/**
 * Get geofence events
 */
function geofence_get_events(int $geofenceId, ?int $tenantId = null, ?int $deviceId = null, ?string $startDate = null, ?string $endDate = null, int $limit = 100): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $where = ["ge.tenant_id = :tenant_id", "ge.geofence_id = :geofence_id"];
    $params = ['tenant_id' => $tenantId, 'geofence_id' => $geofenceId];
    
    if ($deviceId) {
        $where[] = "ge.device_id = :device_id";
        $params['device_id'] = $deviceId;
    }
    
    if ($startDate) {
        $where[] = "DATE(ge.timestamp) >= :start_date";
        $params['start_date'] = $startDate;
    }
    
    if ($endDate) {
        $where[] = "DATE(ge.timestamp) <= :end_date";
        $params['end_date'] = $endDate;
    }
    
    $sql = "SELECT ge.*, d.device_uid, a.name as asset_name
            FROM geofence_events ge
            INNER JOIN devices d ON ge.device_id = d.id
            LEFT JOIN assets a ON d.asset_id = a.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY ge.timestamp DESC
            LIMIT :limit";
    
    return db_fetch_all($sql, $params);
}

/**
 * Get geofence analytics (visits, duration, frequency)
 */
function geofence_get_analytics(int $geofenceId, ?int $tenantId = null, ?string $startDate = null, ?string $endDate = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $where = ["ge.tenant_id = :tenant_id", "ge.geofence_id = :geofence_id"];
    $params = ['tenant_id' => $tenantId, 'geofence_id' => $geofenceId];
    
    if ($startDate) {
        $where[] = "DATE(ge.timestamp) >= :start_date";
        $params['start_date'] = $startDate;
    }
    
    if ($endDate) {
        $where[] = "DATE(ge.timestamp) <= :end_date";
        $params['end_date'] = $endDate;
    }
    
    // Get visit statistics
    $visits = db_fetch_all(
        "SELECT 
            d.id as device_id,
            d.device_uid,
            a.name as asset_name,
            COUNT(CASE WHEN ge.event_type = 'entry' THEN 1 END) as entry_count,
            COUNT(CASE WHEN ge.event_type = 'exit' THEN 1 END) as exit_count,
            MIN(ge.timestamp) as first_visit,
            MAX(ge.timestamp) as last_visit
         FROM geofence_events ge
         INNER JOIN devices d ON ge.device_id = d.id
         LEFT JOIN assets a ON d.asset_id = a.id
         WHERE " . implode(' AND ', $where) . "
         GROUP BY d.id, d.device_uid, a.name
         ORDER BY entry_count DESC",
        $params
    );
    
    // Calculate dwell times (time between entry and exit)
    $dwellWhere = ["ge1.tenant_id = :tenant_id", "ge1.geofence_id = :geofence_id", "ge1.event_type = 'entry'"];
    $dwellParams = ['tenant_id' => $tenantId, 'geofence_id' => $geofenceId];
    
    if ($startDate) {
        $dwellWhere[] = "DATE(ge1.timestamp) >= :start_date";
        $dwellParams['start_date'] = $startDate;
    }
    
    if ($endDate) {
        $dwellWhere[] = "DATE(ge1.timestamp) <= :end_date";
        $dwellParams['end_date'] = $endDate;
    }
    
    $dwellTimes = db_fetch_all(
        "SELECT 
            ge1.device_id,
            ge1.timestamp as entry_time,
            MIN(ge2.timestamp) as exit_time,
            TIMESTAMPDIFF(MINUTE, ge1.timestamp, MIN(ge2.timestamp)) as dwell_minutes
         FROM geofence_events ge1
         INNER JOIN geofence_events ge2 ON ge1.device_id = ge2.device_id 
             AND ge1.geofence_id = ge2.geofence_id
             AND ge2.event_type = 'exit'
             AND ge2.timestamp > ge1.timestamp
         WHERE " . implode(' AND ', $dwellWhere) . "
         GROUP BY ge1.device_id, ge1.timestamp
         ORDER BY ge1.timestamp DESC",
        $dwellParams
    );
    
    // Calculate average dwell time per device
    $avgDwellTimes = [];
    foreach ($dwellTimes as $dwell) {
        $deviceId = $dwell['device_id'];
        if (!isset($avgDwellTimes[$deviceId])) {
            $avgDwellTimes[$deviceId] = ['total_minutes' => 0, 'visit_count' => 0];
        }
        if ($dwell['dwell_minutes'] !== null) {
            $avgDwellTimes[$deviceId]['total_minutes'] += $dwell['dwell_minutes'];
            $avgDwellTimes[$deviceId]['visit_count']++;
        }
    }
    
    foreach ($avgDwellTimes as $deviceId => &$stats) {
        $stats['avg_minutes'] = $stats['visit_count'] > 0 ? round($stats['total_minutes'] / $stats['visit_count'], 2) : 0;
    }
    
    return [
        'visits' => $visits,
        'dwell_times' => $dwellTimes,
        'avg_dwell_times' => $avgDwellTimes,
        'total_entries' => count(array_filter($visits, function($v) { return $v['entry_count'] > 0; })),
        'total_exits' => count(array_filter($visits, function($v) { return $v['exit_count'] > 0; }))
    ];
}

/**
 * Get devices currently inside geofence
 */
function geofence_get_devices_inside(int $geofenceId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT 
                dgs.*,
                d.device_uid,
                d.imei,
                a.name as asset_name,
                TIMESTAMPDIFF(MINUTE, dgs.entry_time, NOW()) as dwell_minutes
            FROM device_geofence_state dgs
            INNER JOIN devices d ON dgs.device_id = d.id
            LEFT JOIN assets a ON d.asset_id = a.id
            WHERE dgs.geofence_id = :geofence_id 
            AND dgs.tenant_id = :tenant_id
            AND dgs.is_inside = 1
            ORDER BY dgs.entry_time DESC";
    
    return db_fetch_all($sql, ['geofence_id' => $geofenceId, 'tenant_id' => $tenantId]);
}

