<?php

/**
 * API: Schema Comparison
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/schema_comparison.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $comparison = schema_compare();
        json_response($comparison);
        break;
        
    case 'POST':
        // Update schema.sql to match current database
        if (schema_update_seed_file()) {
            json_response(['message' => 'Schema file updated successfully']);
        } else {
            json_response(['error' => 'Failed to update schema file'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

