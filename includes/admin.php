<?php

/**
 * Admin Functions (Procedural)
 * Administrative operations for tenants, users, devices
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get all tenants
 */
function admin_get_tenants(): array
{
    return db_fetch_all("SELECT * FROM tenants ORDER BY name ASC");
}

/**
 * Get tenant by ID
 */
function admin_get_tenant(int $id): ?array
{
    return db_fetch_one("SELECT * FROM tenants WHERE id = :id", ['id' => $id]);
}

/**
 * Create tenant
 */
function admin_create_tenant(array $data): int
{
    db_execute(
        "INSERT INTO tenants (name, region) VALUES (:name, :region)",
        ['name' => $data['name'], 'region' => $data['region'] ?? 'us-east']
    );
    return (int)db_last_insert_id();
}

/**
 * Update tenant
 */
function admin_update_tenant(int $id, array $data): bool
{
    $affected = db_execute(
        "UPDATE tenants SET name = :name, region = :region WHERE id = :id",
        [
            'id' => $id,
            'name' => $data['name'],
            'region' => $data['region'] ?? 'us-east'
        ]
    );
    return $affected > 0;
}

/**
 * Delete tenant
 */
function admin_delete_tenant(int $id): bool
{
    $affected = db_execute("DELETE FROM tenants WHERE id = :id", ['id' => $id]);
    return $affected > 0;
}

/**
 * Get all users (across all tenants)
 */
function admin_get_users(array $filters = []): array
{
    $where = [];
    $params = [];
    
    if (isset($filters['tenant_id'])) {
        $where[] = "u.tenant_id = :tenant_id";
        $params['tenant_id'] = $filters['tenant_id'];
    }
    
    if (isset($filters['email'])) {
        $where[] = "u.email LIKE :email";
        $params['email'] = '%' . $filters['email'] . '%';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT u.*, t.name as tenant_name 
            FROM users u 
            LEFT JOIN tenants t ON u.tenant_id = t.id 
            {$whereClause}
            ORDER BY u.email ASC";
    
    return db_fetch_all($sql, $params);
}

/**
 * Get user by ID
 */
function admin_get_user(int $id): ?array
{
    return db_fetch_one(
        "SELECT u.*, t.name as tenant_name FROM users u LEFT JOIN tenants t ON u.tenant_id = t.id WHERE u.id = :id",
        ['id' => $id]
    );
}

/**
 * Create user
 */
function admin_create_user(array $data): int
{
    db_execute(
        "INSERT INTO users (tenant_id, email, role, sso_provider, sso_subject) 
         VALUES (:tenant_id, :email, :role, :sso_provider, :sso_subject)",
        [
            'tenant_id' => $data['tenant_id'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'Guest',
            'sso_provider' => $data['sso_provider'] ?? null,
            'sso_subject' => $data['sso_subject'] ?? null
        ]
    );
    return (int)db_last_insert_id();
}

/**
 * Update user
 */
function admin_update_user(int $id, array $data): bool
{
    $fields = [];
    $params = ['id' => $id];
    
    if (isset($data['email'])) {
        $fields[] = "email = :email";
        $params['email'] = $data['email'];
    }
    if (isset($data['role'])) {
        $fields[] = "role = :role";
        $params['role'] = $data['role'];
    }
    if (isset($data['tenant_id'])) {
        $fields[] = "tenant_id = :tenant_id";
        $params['tenant_id'] = $data['tenant_id'];
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete user
 */
function admin_delete_user(int $id): bool
{
    $affected = db_execute("DELETE FROM users WHERE id = :id", ['id' => $id]);
    return $affected > 0;
}

/**
 * Get all devices (across all tenants)
 */
function admin_get_devices(array $filters = []): array
{
    $where = [];
    $params = [];
    
    if (isset($filters['tenant_id'])) {
        $where[] = "d.tenant_id = :tenant_id";
        $params['tenant_id'] = $filters['tenant_id'];
    }
    
    if (isset($filters['imei'])) {
        $where[] = "d.imei LIKE :imei";
        $params['imei'] = '%' . $filters['imei'] . '%';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT d.*, t.name as tenant_name, a.name as asset_name
            FROM devices d
            LEFT JOIN tenants t ON d.tenant_id = t.id
            LEFT JOIN assets a ON d.asset_id = a.id
            {$whereClause}
            ORDER BY d.device_uid ASC";
    
    return db_fetch_all($sql, $params);
}

/**
 * Get device by ID
 */
function admin_get_device(int $id): ?array
{
    return db_fetch_one(
        "SELECT d.*, t.name as tenant_name, a.name as asset_name 
         FROM devices d 
         LEFT JOIN tenants t ON d.tenant_id = t.id 
         LEFT JOIN assets a ON d.asset_id = a.id 
         WHERE d.id = :id",
        ['id' => $id]
    );
}

/**
 * Create device
 */
function admin_create_device(array $data): int
{
    db_execute(
        "INSERT INTO devices (tenant_id, device_uid, imei, iccid, model, firmware_version, asset_id) 
         VALUES (:tenant_id, :device_uid, :imei, :iccid, :model, :firmware_version, :asset_id)",
        [
            'tenant_id' => $data['tenant_id'],
            'device_uid' => $data['device_uid'],
            'imei' => $data['imei'],
            'iccid' => $data['iccid'] ?? null,
            'model' => $data['model'] ?? 'FMM13A',
            'firmware_version' => $data['firmware_version'] ?? null,
            'asset_id' => $data['asset_id'] ?? null
        ]
    );
    return (int)db_last_insert_id();
}

/**
 * Update device
 */
function admin_update_device(int $id, array $data): bool
{
    $fields = [];
    $params = ['id' => $id];
    
    $allowedFields = ['device_uid', 'imei', 'iccid', 'model', 'firmware_version', 'asset_id', 'tenant_id'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "{$field} = :{$field}";
            $params[$field] = $data[$field];
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE devices SET " . implode(', ', $fields) . " WHERE id = :id";
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete device
 */
function admin_delete_device(int $id): bool
{
    $affected = db_execute("DELETE FROM devices WHERE id = :id", ['id' => $id]);
    return $affected > 0;
}

