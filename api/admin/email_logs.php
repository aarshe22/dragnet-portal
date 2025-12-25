<?php

/**
 * API: Email Logs
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/email_log.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        $tenantId = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $provider = isset($_GET['provider']) ? $_GET['provider'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $logId = isset($_GET['log_id']) ? (int)$_GET['log_id'] : null;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at DESC';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // If log_id is provided, fetch that specific log
        if ($logId) {
            try {
                $log = db_fetch_one("SELECT * FROM email_logs WHERE id = :id", ['id' => $logId]);
                if ($log) {
                    // Decode JSON fields
                    if (!empty($log['response_data'])) {
                        $decoded = json_decode($log['response_data'], true);
                        $log['response_data'] = ($decoded !== null) ? $decoded : $log['response_data'];
                    } else {
                        $log['response_data'] = null;
                    }
                    if (!empty($log['debug_data'])) {
                        $decoded = json_decode($log['debug_data'], true);
                        $log['debug_data'] = ($decoded !== null) ? $decoded : $log['debug_data'];
                    } else {
                        $log['debug_data'] = null;
                    }
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'logs' => [$log],
                        'total' => 1,
                        'page' => 1,
                        'limit' => 1,
                        'pages' => 1
                    ], JSON_PRETTY_PRINT);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'logs' => [],
                        'total' => 0,
                        'page' => 1,
                        'limit' => 1,
                        'pages' => 0
                    ], JSON_PRETTY_PRINT);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
            }
            exit;
        }
        
        // Validate status
        if ($status && !in_array($status, ['pending', 'sent', 'failed', 'bounced'])) {
            $status = null;
        }
        
        // Validate sort
        $allowedSorts = ['created_at DESC', 'created_at ASC', 'sent_at DESC', 'sent_at ASC', 'recipient ASC', 'recipient DESC', 'status ASC', 'status DESC'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at DESC';
        }
        
        try {
            $logs = get_email_logs($tenantId, $status, null, $provider, $search, $limit, $offset, $sort);
            $total = get_email_log_count($tenantId, $status, null, $provider, $search);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch email logs: ' . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
        exit;
        
    case 'DELETE':
        // Clear logs
        $daysToKeep = isset($_GET['days']) ? (int)$_GET['days'] : 0;
        
        if ($daysToKeep > 0) {
            $deleted = cleanup_email_logs($daysToKeep);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => "Deleted $deleted old log entries",
                'deleted' => $deleted
            ], JSON_PRETTY_PRINT);
        } else {
            // Delete all logs
            try {
                $deleted = db_execute("DELETE FROM email_logs");
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Deleted all $deleted log entries",
                    'deleted' => $deleted
                ], JSON_PRETTY_PRINT);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to delete logs: ' . $e->getMessage()
                ], JSON_PRETTY_PRINT);
            }
        }
        exit;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
}

