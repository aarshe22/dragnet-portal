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
    
    // Support filtering by asset_id or device_id
    // Check if asset_id column exists in alerts table
    $hasAssetId = false;
    try {
        $columnCheck = db_fetch_one(
            "SELECT 1 FROM information_schema.columns 
             WHERE table_schema = DATABASE() 
             AND table_name = 'alerts' 
             AND column_name = 'asset_id'"
        );
        $hasAssetId = (bool)$columnCheck;
    } catch (Exception $e) {
        // Column doesn't exist yet
    }
    
    if (isset($filters['asset_id']) && $hasAssetId) {
        $where[] = "a.asset_id = :asset_id";
        $params['asset_id'] = $filters['asset_id'];
    }
    
    if (isset($filters['device_id'])) {
        $where[] = "a.device_id = :device_id";
        $params['device_id'] = $filters['device_id'];
    }
    
    // Build SQL query - use simpler JOIN that doesn't reference a.asset_id if column doesn't exist
    if ($hasAssetId) {
        $sql = "SELECT a.*, 
                       d.device_uid, d.imei,
                       ast.name as asset_name, ast.id as asset_id_display
                FROM alerts a
                LEFT JOIN devices d ON a.device_id = d.id
                LEFT JOIN assets ast ON (a.asset_id = ast.id OR d.asset_id = ast.id)
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.created_at DESC";
    } else {
        // Column doesn't exist, only join via devices
        $sql = "SELECT a.*, 
                       d.device_uid, d.imei,
                       ast.name as asset_name, ast.id as asset_id_display
                FROM alerts a
                LEFT JOIN devices d ON a.device_id = d.id
                LEFT JOIN assets ast ON d.asset_id = ast.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.created_at DESC";
    }
    
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
 * Create alert (supports both device_id and asset_id)
 */
function alert_create(array $data, ?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Validate: either device_id or asset_id must be set
    if (empty($data['device_id']) && empty($data['asset_id'])) {
        throw new Exception('Either device_id or asset_id must be provided');
    }
    
    $sql = "INSERT INTO alerts 
            (tenant_id, device_id, asset_id, type, severity, message, metadata, created_at)
            VALUES 
            (:tenant_id, :device_id, :asset_id, :type, :severity, :message, :metadata, NOW())";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'device_id' => $data['device_id'] ?? null,
        'asset_id' => $data['asset_id'] ?? null,
        'type' => $data['type'],
        'severity' => $data['severity'] ?? 'warning',
        'message' => $data['message'] ?? null,
        'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
    ]);
    
    $alertId = (int)db_last_insert_id();
    
    // Notify subscribed users
    alert_notify_subscribers($alertId, $tenantId);
    
    return $alertId;
}

/**
 * Notify users subscribed to this alert
 */
function alert_notify_subscribers(int $alertId, int $tenantId): void
{
    $alert = alert_find($alertId, $tenantId);
    if (!$alert) {
        return;
    }
    
    // Get subscriptions matching this alert
    $where = ["uas.tenant_id = :tenant_id", "uas.enabled = 1"];
    $params = ['tenant_id' => $tenantId];
    
    // Match by type
    if ($alert['type']) {
        $where[] = "(uas.alert_type IS NULL OR uas.alert_type = :alert_type)";
        $params['alert_type'] = $alert['type'];
    }
    
    // Match by severity
    if ($alert['severity']) {
        $where[] = "(uas.severity IS NULL OR uas.severity = :severity)";
        $params['severity'] = $alert['severity'];
    }
    
    // Match by asset or device
    if ($alert['asset_id']) {
        $where[] = "(uas.asset_id IS NULL OR uas.asset_id = :asset_id)";
        $params['asset_id'] = $alert['asset_id'];
    } elseif ($alert['device_id']) {
        $where[] = "(uas.device_id IS NULL OR uas.device_id = :device_id)";
        $params['device_id'] = $alert['device_id'];
    }
    
    $subscriptions = db_fetch_all(
        "SELECT uas.*, u.email, ps.endpoint, ps.p256dh_key, ps.auth_key
         FROM user_alert_subscriptions uas
         INNER JOIN users u ON uas.user_id = u.id
         LEFT JOIN push_subscriptions ps ON u.id = ps.user_id
         WHERE " . implode(' AND ', $where),
        $params
    );
    
    foreach ($subscriptions as $subscription) {
        // Send push notification if enabled
        if ($subscription['push_notifications'] && $subscription['endpoint']) {
            require_once __DIR__ . '/push.php';
            push_send_notification(
                $subscription['endpoint'],
                $subscription['p256dh_key'],
                $subscription['auth_key'],
                $alert['type'],
                $alert['message'] ?? 'New alert',
                json_encode(['alert_id' => $alertId])
            );
        }
        
        // Send email if enabled
        if ($subscription['email_notifications'] && $subscription['email']) {
            require_once __DIR__ . '/email.php';
            email_send_alert($subscription['email'], $alert);
        }
    }
}

