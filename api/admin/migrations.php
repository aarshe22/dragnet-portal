<?php

/**
 * API: Database Migrations
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/migrations.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        // Get migration status
        $status = get_migration_status();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'migrations' => $status,
            'migrations_table_exists' => migrations_table_exists()
        ], JSON_PRETTY_PRINT);
        exit;
        
    case 'POST':
        // Execute a migration
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['filename'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Filename is required']);
            exit;
        }
        
        $filename = $data['filename'];
        $files = get_migration_files();
        
        // Find the migration file
        $migrationFile = null;
        foreach ($files as $file) {
            if ($file['filename'] === $filename) {
                $migrationFile = $file;
                break;
            }
        }
        
        if (!$migrationFile) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Migration file not found']);
            exit;
        }
        
        // Check if already applied
        $applied = is_migration_applied($filename);
        if ($applied && $applied['status'] === 'success') {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Migration has already been applied successfully',
                'applied_at' => $applied['applied_at']
            ]);
            exit;
        }
        
        // Get current user ID
        $context = get_tenant_context();
        $userId = $context ? $context['user_id'] : null;
        
        // Execute the migration
        $result = execute_migration($filename, $migrationFile['path'], $userId);
        
        if ($result['success']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Migration applied successfully',
                'execution_time' => $result['execution_time'],
                'rows_affected' => $result['rows_affected']
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $result['error'],
                'execution_time' => $result['execution_time']
            ], JSON_PRETTY_PRINT);
        }
        exit;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
}

