<?php

/**
 * Model Functions (Procedural)
 * All database operations for entities
 */

/**
 * Generic model functions
 */
function model_find(string $table, int $id, ?int $tenantId = null): ?array
{
    $where = ["id = :id"];
    $params = ['id' => $id];
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where);
    return db_fetch_one($sql, $params);
}

function model_find_all(string $table, array $conditions = [], string $orderBy = 'id DESC', ?int $tenantId = null): array
{
    $where = [];
    $params = [];
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    foreach ($conditions as $key => $value) {
        $where[] = "{$key} = :{$key}";
        $params[$key] = $value;
    }
    
    $sql = "SELECT * FROM {$table}";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY {$orderBy}";
    
    return db_fetch_all($sql, $params);
}

function model_create(string $table, array $data, ?int $tenantId = null): int
{
    if ($tenantId !== null) {
        $data['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $data['tenant_id'] = $context['tenant_id'];
        }
    }
    
    $fields = array_keys($data);
    $placeholders = array_map(fn($f) => ":{$f}", $fields);
    
    $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    db_execute($sql, $data);
    return (int)db_last_insert_id();
}

function model_update(string $table, int $id, array $data, ?int $tenantId = null): bool
{
    $where = ["id = :id"];
    $params = array_merge($data, ['id' => $id]);
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    $fields = array_keys($data);
    $set = array_map(fn($f) => "{$f} = :{$f}", $fields);
    
    $sql = "UPDATE {$table} SET " . implode(', ', $set) . 
           " WHERE " . implode(' AND ', $where);
    
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

function model_delete(string $table, int $id, ?int $tenantId = null): bool
{
    $where = ["id = :id"];
    $params = ['id' => $id];
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $where);
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

function model_count(string $table, array $conditions = [], ?int $tenantId = null): int
{
    $where = [];
    $params = [];
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    foreach ($conditions as $key => $value) {
        $where[] = "{$key} = :{$key}";
        $params[$key] = $value;
    }
    
    $sql = "SELECT COUNT(*) as count FROM {$table}";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    
    $result = db_fetch_one($sql, $params);
    return (int)($result['count'] ?? 0);
}

/**
 * User functions
 */
function user_find_by_email(string $email, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $context = get_tenant_context();
        if (!$context) {
            throw new Exception('Tenant ID required');
        }
        $tenantId = $context['tenant_id'];
    }
    
    $sql = "SELECT * FROM users WHERE email = :email AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['email' => $email, 'tenant_id' => $tenantId]);
}

function user_find_or_create_from_sso(string $email, int $tenantId, string $provider, string $subject, string $role = 'Guest'): array
{
    $user = user_find_by_email($email, $tenantId);
    
    if ($user) {
        // Update last login
        model_update('users', $user['id'], [
            'last_login' => date('Y-m-d H:i:s'),
            'sso_provider' => $provider,
            'sso_subject' => $subject
        ], $tenantId);
        return model_find('users', $user['id'], $tenantId);
    }
    
    // Create new user
    $userId = model_create('users', [
        'email' => $email,
        'role' => $role,
        'sso_provider' => $provider,
        'sso_subject' => $subject,
        'last_login' => date('Y-m-d H:i:s')
    ], $tenantId);
    
    return model_find('users', $userId, $tenantId);
}

/**
 * Device functions
 */
function device_find_by_imei(string $imei, ?int $tenantId = null): ?array
{
    $where = ["imei = :imei"];
    $params = ['imei' => $imei];
    
    if ($tenantId !== null) {
        $where[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $tenantId;
    } else {
        $context = get_tenant_context();
        if ($context) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $context['tenant_id'];
        }
    }
    
    $sql = "SELECT * FROM devices WHERE " . implode(' AND ', $where);
    return db_fetch_one($sql, $params);
}

function device_get_latest_telemetry(int $deviceId): ?array
{
    $sql = "SELECT * FROM telemetry 
            WHERE device_id = :device_id 
            ORDER BY timestamp DESC 
            LIMIT 1";
    
    return db_fetch_one($sql, ['device_id' => $deviceId]);
}

function device_find_all_with_status(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $context = get_tenant_context();
        if (!$context) {
            return [];
        }
        $tenantId = $context['tenant_id'];
    }
    
    $sql = "SELECT d.*, 
                   (SELECT lat FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lat,
                   (SELECT lon FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lon,
                   (SELECT speed FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as speed,
                   (SELECT timestamp FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as last_position_time
            FROM devices d
            WHERE d.tenant_id = :tenant_id
            ORDER BY d.last_checkin DESC";
    
    return db_fetch_all($sql, ['tenant_id' => $tenantId]);
}

function device_update_status(int $deviceId): void
{
    $device = model_find('devices', $deviceId);
    if (!$device) {
        return;
    }
    
    $lastCheckin = strtotime($device['last_checkin'] ?? '1970-01-01');
    $now = time();
    $minutesSinceCheckin = ($now - $lastCheckin) / 60;
    
    $status = 'online';
    if ($minutesSinceCheckin > 15) {
        $status = 'offline';
    }
    
    model_update('devices', $deviceId, ['status' => $status], $device['tenant_id']);
}

function device_register_teltonika(string $imei, int $tenantId, string $deviceUid = null): array
{
    // Check if already exists
    $existing = device_find_by_imei($imei, $tenantId);
    if ($existing) {
        return $existing;
    }
    
    // Create new device
    $deviceUid = $deviceUid ?? 'TELTONIKA_' . $imei;
    
    $deviceId = model_create('devices', [
        'device_uid' => $deviceUid,
        'imei' => $imei,
        'device_type' => 'teltonika_fmm13a',
        'protocol' => 'http',
        'status' => 'offline',
    ], $tenantId);
    
    return model_find('devices', $deviceId, $tenantId);
}

/**
 * Asset functions
 */
function asset_find_with_device(int $assetId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $context = get_tenant_context();
        if (!$context) {
            return null;
        }
        $tenantId = $context['tenant_id'];
    }
    
    $sql = "SELECT a.*, d.device_uid, d.imei, d.status as device_status, 
                   d.last_checkin, d.battery_level, d.signal_strength
            FROM assets a
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE a.id = :id AND a.tenant_id = :tenant_id";
    
    return db_fetch_one($sql, ['id' => $assetId, 'tenant_id' => $tenantId]);
}

function asset_find_all_with_devices(array $conditions = [], ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $context = get_tenant_context();
        if (!$context) {
            return [];
        }
        $tenantId = $context['tenant_id'];
    }
    
    $where = ["a.tenant_id = :tenant_id"];
    $params = ['tenant_id' => $tenantId];
    
    foreach ($conditions as $key => $value) {
        $where[] = "a.{$key} = :{$key}";
        $params[$key] = $value;
    }
    
    $sql = "SELECT a.*, d.device_uid, d.status as device_status, d.last_checkin
            FROM assets a
            LEFT JOIN devices d ON a.device_id = d.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.name";
    
    return db_fetch_all($sql, $params);
}

/**
 * Telemetry functions
 */
function telemetry_get_by_device_and_range(int $deviceId, string $startTime, string $endTime): array
{
    // Verify device belongs to tenant
    $device = model_find('devices', $deviceId);
    if (!$device) {
        return [];
    }
    
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
 * Alert functions
 */
function alert_get_unacknowledged(array $filters = [], ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $context = get_tenant_context();
        if (!$context) {
            return [];
        }
        $tenantId = $context['tenant_id'];
    }
    
    $where = ["a.tenant_id = :tenant_id", "a.acknowledged = 0"];
    $params = ['tenant_id' => $tenantId];
    
    if (isset($filters['type'])) {
        $where[] = "a.type = :type";
        $params['type'] = $filters['type'];
    }
    
    if (isset($filters['severity'])) {
        $where[] = "a.severity = :severity";
        $params['severity'] = $filters['severity'];
    }
    
    $sql = "SELECT a.*, d.device_uid, d.imei, ast.name as asset_name
            FROM alerts a
            INNER JOIN devices d ON a.device_id = d.id
            LEFT JOIN assets ast ON d.asset_id = ast.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.created_at DESC";
    
    return db_fetch_all($sql, $params);
}

function alert_acknowledge(int $alertId, int $userId, ?int $tenantId = null): bool
{
    return model_update('alerts', $alertId, [
        'acknowledged' => true,
        'acknowledged_by' => $userId,
        'acknowledged_at' => date('Y-m-d H:i:s')
    ], $tenantId);
}

/**
 * Geofence functions
 */
function geofence_find_all_active(?int $tenantId = null): array
{
    return model_find_all('geofences', ['active' => true], 'name ASC', $tenantId);
}

/**
 * Trip functions
 */
function trip_get_by_asset(int $assetId, string $startDate = null, string $endDate = null): array
{
    $where = ["asset_id = :asset_id"];
    $params = ['asset_id' => $assetId];
    
    if ($startDate) {
        $where[] = "start_time >= :start_date";
        $params['start_date'] = $startDate;
    }
    
    if ($endDate) {
        $where[] = "end_time <= :end_date";
        $params['end_date'] = $endDate;
    }
    
    $sql = "SELECT * FROM trips 
            WHERE " . implode(' AND ', $where) . "
            ORDER BY start_time DESC";
    
    return db_fetch_all($sql, $params);
}

