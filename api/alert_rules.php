<?php

/**
 * API: Alert Rules Management
 */

// Load configuration first
$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/alert_rules.php';
require_once __DIR__ . '/../includes/alert_types.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Operator');

$method = $_SERVER['REQUEST_METHOD'];
$tenantId = require_tenant();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'types') {
            // Return available alert types
            json_response(get_alert_types());
        } elseif ($action === 'types_by_category') {
            // Return alert types grouped by category
            json_response(get_alert_types_by_category());
        } else {
            $ruleId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($ruleId) {
                $rule = alert_rule_find($ruleId, $tenantId);
                if (!$rule) {
                    json_response(['error' => 'Rule not found'], 404);
                }
                
                // Get associated devices and groups
                $rule['devices'] = alert_rule_get_devices($ruleId, $tenantId);
                $rule['groups'] = alert_rule_get_groups($ruleId, $tenantId);
                json_response($rule);
            } else {
                $rules = alert_rule_list_all($tenantId);
                json_response($rules);
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        if (empty($data['name']) || empty($data['alert_type'])) {
            json_response(['error' => 'Name and alert type are required'], 400);
        }
        
        $ruleId = alert_rule_create($data, $tenantId);
        
        // Add devices if provided
        if (!empty($data['device_ids']) && is_array($data['device_ids'])) {
            foreach ($data['device_ids'] as $deviceId) {
                alert_rule_add_device($ruleId, (int)$deviceId, $tenantId);
            }
        }
        
        // Add groups if provided
        if (!empty($data['group_ids']) && is_array($data['group_ids'])) {
            foreach ($data['group_ids'] as $groupId) {
                alert_rule_add_group($ruleId, (int)$groupId, $tenantId);
            }
        }
        
        json_response(['success' => true, 'id' => $ruleId, 'message' => 'Alert rule created']);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $ruleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$ruleId) {
            json_response(['error' => 'Rule ID required'], 400);
        }
        
        if (alert_rule_update($ruleId, $data, $tenantId)) {
            json_response(['success' => true, 'message' => 'Alert rule updated']);
        } else {
            json_response(['error' => 'Failed to update alert rule'], 500);
        }
        break;
        
    case 'DELETE':
        $ruleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$ruleId) {
            json_response(['error' => 'Rule ID required'], 400);
        }
        
        if (alert_rule_delete($ruleId, $tenantId)) {
            json_response(['success' => true, 'message' => 'Alert rule deleted']);
        } else {
            json_response(['error' => 'Failed to delete alert rule'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

