<?php

/**
 * Alert Functions (Procedural)
 * Alert management with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get alerts for tenant
 */
function alert_list_all(array $filters = [], ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $where = ["a.tenant_id = :tenant_id"];
    $params = ['tenant_id' => $tenantId];
    
    if (isset($filters['type'])) {
        $where[] = "a.type = :type";
        $params['type'] = $filters['type'];
    }
    
    if (isset($filters['severity'])) {
        $where[] = "a.severity = :severity";
        $params['severity'] = $filters['severity'];
    }
    
    if (isset($filters['acknowledged'])) {
        $where[] = "a.acknowledged = :acknowledged";
        $params['acknowledged'] = $filters['acknowledged'] ? 1 : 0;
    }
    
    $sql = "SELECT a.*, d.device_uid, d.imei, ast.name as asset_name
            FROM alerts a
            INNER JOIN devices d ON a.device_id = d.id
            LEFT JOIN assets ast ON d.asset_id = ast.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.created_at DESC";
    
    return db_fetch_all($sql, $params);
}

/**
 * Get unacknowledged alerts
 */
function alert_list_unacknowledged(array $filters = [], ?int $tenantId = null): array
{
    $filters['acknowledged'] = false;
    return alert_list_all($filters, $tenantId);
}

/**
 * Find alert by ID
 */
function alert_find(int $alertId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM alerts WHERE id = :id AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['id' => $alertId, 'tenant_id' => $tenantId]);
}

/**
 * Acknowledge alert
 */
function alert_acknowledge(int $alertId, int $userId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $affected = db_execute(
        "UPDATE alerts SET acknowledged = 1, acknowledged_by = :user_id, acknowledged_at = NOW() 
         WHERE id = :id AND tenant_id = :tenant_id",
        ['id' => $alertId, 'user_id' => $userId, 'tenant_id' => $tenantId]
    );
    
    return $affected > 0;
}

/**
 * Count alerts
 */
function alert_count(array $filters = [], ?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $where = ["tenant_id = :tenant_id"];
    $params = ['tenant_id' => $tenantId];
    
    foreach ($filters as $key => $value) {
        $where[] = "{$key} = :{$key}";
        $params[$key] = $value;
    }
    
    $sql = "SELECT COUNT(*) as count FROM alerts WHERE " . implode(' AND ', $where);
    $result = db_fetch_one($sql, $params);
    
    return (int)($result['count'] ?? 0);
}

/**
 * Create alert
 */
function alert_create(array $data, ?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "INSERT INTO alerts 
            (tenant_id, device_id, type, severity, message, metadata, created_at)
            VALUES 
            (:tenant_id, :device_id, :type, :severity, :message, :metadata, NOW())";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'device_id' => $data['device_id'],
        'type' => $data['type'],
        'severity' => $data['severity'] ?? 'warning',
        'message' => $data['message'] ?? null,
        'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
    ]);
    
    return (int)db_last_insert_id();
}

