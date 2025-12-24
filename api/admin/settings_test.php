<?php

/**
 * API: Settings Test - Diagnostic endpoint
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

$config = $GLOBALS['config'];

try {
    db_init($config['database']);
    
    // Check if table exists
    $tableExists = false;
    try {
        db_fetch_one("SELECT 1 FROM settings LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {
        $tableExists = false;
    }
    
    // Try to insert a test record
    $testResult = 'Not tested';
    if ($tableExists) {
        try {
            db_execute(
                "INSERT INTO settings (tenant_id, setting_key, setting_value) 
                 VALUES (NULL, 'test_key', 'test_value')
                 ON DUPLICATE KEY UPDATE setting_value = 'test_value'",
                []
            );
            $testResult = 'Success';
        } catch (Exception $e) {
            $testResult = 'Failed: ' . $e->getMessage();
        }
    }
    
    json_response([
        'table_exists' => $tableExists,
        'test_insert' => $testResult,
        'database' => $config['database']['name'],
    ]);
} catch (Exception $e) {
    json_response([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], 500);
}

