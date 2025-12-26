<?php

/**
 * Alert Rules Functions (Procedural)
 * Alert rule management with tenant scoping
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get all alert rules for tenant
 */
function alert_rule_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM alert_rules WHERE tenant_id = :tenant_id ORDER BY name ASC";
    $rules = db_fetch_all($sql, ['tenant_id' => $tenantId]);
    
    // Decode JSON fields
    foreach ($rules as &$rule) {
        $rule['conditions'] = $rule['conditions'] ? json_decode($rule['conditions'], true) : null;
        $rule['actions'] = $rule['actions'] ? json_decode($rule['actions'], true) : null;
        $rule['notification_recipients'] = $rule['notification_recipients'] ? json_decode($rule['notification_recipients'], true) : null;
    }
    
    return $rules;
}

/**
 * Find alert rule by ID (tenant-scoped)
 */
function alert_rule_find(int $ruleId, ?int $tenantId = null): ?array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT * FROM alert_rules WHERE id = :id AND tenant_id = :tenant_id";
    $rule = db_fetch_one($sql, ['id' => $ruleId, 'tenant_id' => $tenantId]);
    
    if ($rule) {
        $rule['conditions'] = $rule['conditions'] ? json_decode($rule['conditions'], true) : null;
        $rule['actions'] = $rule['actions'] ? json_decode($rule['actions'], true) : null;
        $rule['notification_recipients'] = $rule['notification_recipients'] ? json_decode($rule['notification_recipients'], true) : null;
    }
    
    return $rule;
}

/**
 * Create a new alert rule
 */
function alert_rule_create(array $data, int $tenantId): int
{
    $sql = "INSERT INTO alert_rules (tenant_id, name, description, alert_type, severity, enabled, threshold_value, threshold_unit, conditions, actions, notification_recipients) 
            VALUES (:tenant_id, :name, :description, :alert_type, :severity, :enabled, :threshold_value, :threshold_unit, :conditions, :actions, :notification_recipients)";
    
    db_execute($sql, [
        'tenant_id' => $tenantId,
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'alert_type' => $data['alert_type'],
        'severity' => $data['severity'] ?? 'warning',
        'enabled' => $data['enabled'] ?? 1,
        'threshold_value' => $data['threshold_value'] ?? null,
        'threshold_unit' => $data['threshold_unit'] ?? null,
        'conditions' => isset($data['conditions']) ? json_encode($data['conditions']) : null,
        'actions' => isset($data['actions']) ? json_encode($data['actions']) : null,
        'notification_recipients' => isset($data['notification_recipients']) ? json_encode($data['notification_recipients']) : null
    ]);
    
    return (int)db_last_insert_id();
}

/**
 * Update alert rule
 */
function alert_rule_update(int $ruleId, array $data, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $fields = [];
    $params = ['id' => $ruleId, 'tenant_id' => $tenantId];
    
    $allowedFields = ['name', 'description', 'alert_type', 'severity', 'enabled', 'threshold_value', 'threshold_unit', 'conditions', 'actions', 'notification_recipients'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if (in_array($field, ['conditions', 'actions', 'notification_recipients'])) {
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
    
    $sql = "UPDATE alert_rules SET " . implode(', ', $fields) . 
           " WHERE id = :id AND tenant_id = :tenant_id";
    
    $affected = db_execute($sql, $params);
    return $affected > 0;
}

/**
 * Delete alert rule
 */
function alert_rule_delete(int $ruleId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM alert_rules WHERE id = :id AND tenant_id = :tenant_id";
    $affected = db_execute($sql, ['id' => $ruleId, 'tenant_id' => $tenantId]);
    return $affected > 0;
}

/**
 * Get devices associated with alert rule
 */
function alert_rule_get_devices(int $ruleId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT d.* FROM devices d
            INNER JOIN alert_rule_devices ard ON d.id = ard.device_id
            WHERE ard.rule_id = :rule_id AND d.tenant_id = :tenant_id
            ORDER BY d.device_uid ASC";
    
    return db_fetch_all($sql, ['rule_id' => $ruleId, 'tenant_id' => $tenantId]);
}

/**
 * Get groups associated with alert rule
 */
function alert_rule_get_groups(int $ruleId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "SELECT dg.* FROM device_groups dg
            INNER JOIN alert_rule_groups arg ON dg.id = arg.group_id
            WHERE arg.rule_id = :rule_id AND dg.tenant_id = :tenant_id
            ORDER BY dg.name ASC";
    
    return db_fetch_all($sql, ['rule_id' => $ruleId, 'tenant_id' => $tenantId]);
}

/**
 * Add device to alert rule
 */
function alert_rule_add_device(int $ruleId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify rule belongs to tenant
    $rule = alert_rule_find($ruleId, $tenantId);
    if (!$rule) {
        return false;
    }
    
    // Verify device belongs to tenant
    require_once __DIR__ . '/devices.php';
    $device = device_find($deviceId, $tenantId);
    if (!$device) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO alert_rule_devices (rule_id, device_id) 
                VALUES (:rule_id, :device_id)
                ON DUPLICATE KEY UPDATE rule_id = rule_id";
        db_execute($sql, ['rule_id' => $ruleId, 'device_id' => $deviceId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove device from alert rule
 */
function alert_rule_remove_device(int $ruleId, int $deviceId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM alert_rule_devices 
            WHERE rule_id = :rule_id AND device_id = :device_id";
    $affected = db_execute($sql, ['rule_id' => $ruleId, 'device_id' => $deviceId]);
    return $affected > 0;
}

/**
 * Add group to alert rule
 */
function alert_rule_add_group(int $ruleId, int $groupId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    // Verify rule belongs to tenant
    $rule = alert_rule_find($ruleId, $tenantId);
    if (!$rule) {
        return false;
    }
    
    // Verify group belongs to tenant
    require_once __DIR__ . '/device_groups.php';
    $group = device_group_find($groupId, $tenantId);
    if (!$group) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO alert_rule_groups (rule_id, group_id) 
                VALUES (:rule_id, :group_id)
                ON DUPLICATE KEY UPDATE rule_id = rule_id";
        db_execute($sql, ['rule_id' => $ruleId, 'group_id' => $groupId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove group from alert rule
 */
function alert_rule_remove_group(int $ruleId, int $groupId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $sql = "DELETE FROM alert_rule_groups 
            WHERE rule_id = :rule_id AND group_id = :group_id";
    $affected = db_execute($sql, ['rule_id' => $ruleId, 'group_id' => $groupId]);
    return $affected > 0;
}

