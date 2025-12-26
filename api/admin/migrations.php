<?php

/**
 * API: Migration Management
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

$method = $_SERVER['REQUEST_METHOD'];
$context = get_tenant_context();
$userId = $context['user_id'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'status';
        
        if ($action === 'status') {
            $migrations = migrations_get_status();
            json_response($migrations);
        } elseif ($action === 'content') {
            $filename = $_GET['filename'] ?? '';
            if (!$filename) {
                json_response(['error' => 'Filename required'], 400);
            }
            $content = migrations_get_content($filename);
            if ($content === null) {
                json_response(['error' => 'File not found'], 404);
            } else {
                json_response(['filename' => $filename, 'content' => $content]);
            }
        } elseif ($action === 'scan') {
            require_once __DIR__ . '/../../includes/migrations.php';
            $result = migrations_scan_and_mark($userId);
            json_response($result);
        } else {
            json_response(['error' => 'Invalid action'], 400);
        }
        break;
        
    case 'POST':
        $data = input();
        $filename = $data['filename'] ?? '';
        
        if (!$filename) {
            json_response(['error' => 'Filename required'], 400);
        }
        
        // Validate filename (prevent directory traversal)
        if (preg_match('/[\/\\\\]/', $filename) || !preg_match('/\.sql$/', $filename)) {
            json_response(['error' => 'Invalid filename'], 400);
        }
        
        $result = migrations_apply($filename, $userId);
        
        if ($result['success']) {
            json_response($result);
        } else {
            json_response($result, 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

