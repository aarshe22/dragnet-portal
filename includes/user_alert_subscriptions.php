<?php

/**
 * User Alert Subscription Functions
 * Manage user subscriptions to alerts
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get user's alert subscriptions
 */
function user_alert_subscriptions_get(int $userId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    return db_fetch_all(
        "SELECT uas.*, 
                a.name as asset_name,
                d.device_uid,
                d.imei
         FROM user_alert_subscriptions uas
         LEFT JOIN assets a ON uas.asset_id = a.id
         LEFT JOIN devices d ON uas.device_id = d.id
         WHERE uas.user_id = :user_id AND uas.tenant_id = :tenant_id
         ORDER BY uas.created_at DESC",
        ['user_id' => $userId, 'tenant_id' => $tenantId]
    );
}

/**
 * Create or update user alert subscription
 */
function user_alert_subscription_save(array $data, int $userId, ?int $tenantId = null): int
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Check if subscription already exists
    $where = ["user_id = :user_id", "tenant_id = :tenant_id"];
    $params = ['user_id' => $userId, 'tenant_id' => $tenantId];
    
    if (isset($data['alert_type'])) {
        $where[] = "alert_type = :alert_type";
        $params['alert_type'] = $data['alert_type'];
    } else {
        $where[] = "alert_type IS NULL";
    }
    
    if (isset($data['asset_id'])) {
        $where[] = "asset_id = :asset_id";
        $params['asset_id'] = $data['asset_id'];
    } else {
        $where[] = "asset_id IS NULL";
    }
    
    if (isset($data['device_id'])) {
        $where[] = "device_id = :device_id";
        $params['device_id'] = $data['device_id'];
    } else {
        $where[] = "device_id IS NULL";
    }
    
    if (isset($data['severity'])) {
        $where[] = "severity = :severity";
        $params['severity'] = $data['severity'];
    } else {
        $where[] = "severity IS NULL";
    }
    
    $existing = db_fetch_one(
        "SELECT id FROM user_alert_subscriptions WHERE " . implode(' AND ', $where),
        $params
    );
    
    if ($existing) {
        // Update existing
        $sql = "UPDATE user_alert_subscriptions SET
                enabled = :enabled,
                push_notifications = :push_notifications,
                email_notifications = :email_notifications
                WHERE id = :id";
        
        db_execute($sql, [
            'id' => $existing['id'],
            'enabled' => $data['enabled'] ?? 1,
            'push_notifications' => $data['push_notifications'] ?? 1,
            'email_notifications' => $data['email_notifications'] ?? 0
        ]);
        
        return $existing['id'];
    } else {
        // Create new
        $sql = "INSERT INTO user_alert_subscriptions 
                (user_id, tenant_id, alert_type, asset_id, device_id, severity, enabled, push_notifications, email_notifications)
                VALUES 
                (:user_id, :tenant_id, :alert_type, :asset_id, :device_id, :severity, :enabled, :push_notifications, :email_notifications)";
        
        db_execute($sql, [
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'alert_type' => $data['alert_type'] ?? null,
            'asset_id' => $data['asset_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'severity' => $data['severity'] ?? null,
            'enabled' => $data['enabled'] ?? 1,
            'push_notifications' => $data['push_notifications'] ?? 1,
            'email_notifications' => $data['email_notifications'] ?? 0
        ]);
        
        return (int)db_last_insert_id();
    }
}

/**
 * Delete user alert subscription
 */
function user_alert_subscription_delete(int $subscriptionId, int $userId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $affected = db_execute(
        "DELETE FROM user_alert_subscriptions 
         WHERE id = :id AND user_id = :user_id AND tenant_id = :tenant_id",
        ['id' => $subscriptionId, 'user_id' => $userId, 'tenant_id' => $tenantId]
    );
    
    return $affected > 0;
}

