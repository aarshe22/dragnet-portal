<?php

/**
 * Email Logging Functions (Procedural)
 * Functions for logging email sending attempts
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Check if email_logs table exists
 */
function email_logs_table_exists(): bool
{
    try {
        db_fetch_one("SELECT 1 FROM email_logs LIMIT 1");
        return true;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, "doesn't exist") !== false || 
            stripos($msg, "Unknown table") !== false ||
            stripos($msg, "Table") !== false) {
            return false;
        }
        throw $e;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Log an email attempt
 */
function log_email_attempt(
    string $recipient,
    ?string $subject = null,
    ?string $provider = null,
    string $status = 'pending',
    ?string $errorMessage = null,
    ?array $responseData = null,
    ?array $debugData = null,
    ?int $tenantId = null
): ?int {
    if (!email_logs_table_exists()) {
        error_log('Email logs table does not exist');
        return null;
    }
    
    try {
        $context = get_tenant_context();
        $tenantId = $tenantId ?? ($context ? $context['tenant_id'] : null);
        
        $sql = "INSERT INTO email_logs 
                (tenant_id, recipient, subject, provider, status, error_message, response_data, debug_data, sent_at)
                VALUES 
                (:tenant_id, :recipient, :subject, :provider, :status, :error_message, :response_data, :debug_data, :sent_at)";
        
        $params = [
            'tenant_id' => $tenantId,
            'recipient' => $recipient,
            'subject' => $subject,
            'provider' => $provider,
            'status' => $status,
            'error_message' => $errorMessage,
            'response_data' => $responseData ? json_encode($responseData) : null,
            'debug_data' => $debugData ? json_encode($debugData) : null,
            'sent_at' => ($status === 'sent') ? date('Y-m-d H:i:s') : null
        ];
        
        db_execute($sql, $params);
        return db_last_insert_id();
    } catch (Exception $e) {
        error_log('Failed to log email attempt: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get email logs with filtering
 */
function get_email_logs(
    ?int $tenantId = null,
    ?string $status = null,
    ?string $recipient = null,
    ?string $provider = null,
    ?string $search = null,
    int $limit = 100,
    int $offset = 0,
    string $sort = 'created_at DESC'
): array {
    if (!email_logs_table_exists()) {
        return [];
    }
    
    try {
        $context = get_tenant_context();
        $tenantId = $tenantId ?? ($context ? $context['tenant_id'] : null);
        
        $where = [];
        $params = [];
        
        if ($tenantId !== null) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        if ($recipient) {
            $where[] = "recipient LIKE :recipient";
            $params['recipient'] = '%' . $recipient . '%';
        }
        
        if ($provider) {
            $where[] = "provider = :provider";
            $params['provider'] = $provider;
        }
        
        if ($search) {
            $where[] = "(recipient LIKE :search OR subject LIKE :search OR error_message LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Validate sort
        $allowedSorts = ['created_at DESC', 'created_at ASC', 'sent_at DESC', 'sent_at ASC', 'recipient ASC', 'recipient DESC', 'status ASC', 'status DESC'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at DESC';
        }
        
        // LIMIT and OFFSET must be integers, not bound parameters
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $sql = "SELECT * FROM email_logs $whereClause ORDER BY $sort LIMIT $limit OFFSET $offset";
        
        $logs = db_fetch_all($sql, $params);
        
        // Decode JSON fields
        foreach ($logs as &$log) {
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
        }
        
        return $logs;
    } catch (Exception $e) {
        error_log('Failed to get email logs: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get email log count
 */
function get_email_log_count(
    ?int $tenantId = null,
    ?string $status = null,
    ?string $recipient = null,
    ?string $provider = null,
    ?string $search = null
): int {
    if (!email_logs_table_exists()) {
        return 0;
    }
    
    try {
        $context = get_tenant_context();
        $tenantId = $tenantId ?? ($context ? $context['tenant_id'] : null);
        
        $where = [];
        $params = [];
        
        if ($tenantId !== null) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        if ($recipient) {
            $where[] = "recipient LIKE :recipient";
            $params['recipient'] = '%' . $recipient . '%';
        }
        
        if ($provider) {
            $where[] = "provider = :provider";
            $params['provider'] = $provider;
        }
        
        if ($search) {
            $where[] = "(recipient LIKE :search OR subject LIKE :search OR error_message LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as count FROM email_logs $whereClause";
        $result = db_fetch_one($sql, $params);
        return (int)($result['count'] ?? 0);
    } catch (Exception $e) {
        error_log('Failed to get email log count: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Delete old email logs (cleanup)
 */
function cleanup_email_logs(int $daysToKeep = 90): int
{
    if (!email_logs_table_exists()) {
        return 0;
    }
    
    try {
        $sql = "DELETE FROM email_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        return db_execute($sql, ['days' => $daysToKeep]);
    } catch (Exception $e) {
        error_log('Failed to cleanup email logs: ' . $e->getMessage());
        return 0;
    }
}

