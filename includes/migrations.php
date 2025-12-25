<?php

/**
 * Migration Management Functions (Procedural)
 * Functions for managing database migrations
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Check if migrations table exists
 */
function migrations_table_exists(): bool
{
    try {
        $result = db()->query("SELECT 1 FROM migrations LIMIT 1");
        return $result !== false;
    } catch (PDOException $e) {
        $code = $e->getCode();
        // MySQL error code 1146 = Table doesn't exist
        if ($code == 1146 || stripos($e->getMessage(), "doesn't exist") !== false) {
            return false;
        }
        throw $e;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get all migration files from the migrations directory
 */
function get_migration_files(): array
{
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = [];
    
    if (!is_dir($migrationsDir)) {
        return $files;
    }
    
    $items = scandir($migrationsDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $migrationsDir . '/' . $item;
        if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'sql') {
            $files[] = [
                'filename' => $item,
                'path' => $path,
                'size' => filesize($path),
                'modified' => filemtime($path)
            ];
        }
    }
    
    // Sort by filename
    usort($files, function($a, $b) {
        return strcmp($a['filename'], $b['filename']);
    });
    
    return $files;
}

/**
 * Check if a migration has been applied
 */
function is_migration_applied(string $filename): ?array
{
    if (!migrations_table_exists()) {
        return null;
    }
    
    try {
        return db_fetch_one(
            "SELECT * FROM migrations WHERE filename = :filename",
            ['filename' => $filename]
        );
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get all applied migrations
 */
function get_applied_migrations(): array
{
    if (!migrations_table_exists()) {
        return [];
    }
    
    try {
        return db_fetch_all("SELECT * FROM migrations ORDER BY applied_at DESC");
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Execute a migration file
 * Simplified approach: read file and execute directly using PDO::exec()
 */
function execute_migration(string $filename, string $filePath, ?int $userId = null): array
{
    $startTime = microtime(true);
    $result = [
        'success' => false,
        'filename' => $filename,
        'error' => null,
        'execution_time' => null,
        'rows_affected' => 0
    ];
    
    try {
        // Read the SQL file
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: $filename");
        }
        
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception("Failed to read migration file: $filename");
        }
        
        // Clean up SQL: remove comments and normalize
        $sql = preg_replace('/^\s*--.*$/m', '', $sql); // Remove comment lines
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove block comments
        $sql = trim($sql);
        
        if (empty($sql)) {
            throw new Exception("Migration file is empty after removing comments");
        }
        
        // Split into individual statements and execute one by one
        // This is more reliable than trying to execute all at once
        $statements = explode(';', $sql);
        $rowsAffected = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }
            
            // Execute each statement
            $result = db()->exec($statement . ';');
            if ($result !== false) {
                $rowsAffected += $result;
            }
        }
        
        // Ensure migrations table exists for tracking
        if (!migrations_table_exists()) {
            // Create migrations table if it doesn't exist
            $createTableSql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                applied_by INT UNSIGNED NULL,
                execution_time DECIMAL(10, 3) NULL,
                error_message TEXT NULL,
                status ENUM('success', 'failed', 'partial') DEFAULT 'success',
                INDEX idx_filename (filename),
                INDEX idx_applied_at (applied_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            db()->exec($createTableSql);
        }
        
        // Record successful migration
        $executionTime = microtime(true) - $startTime;
        db_execute(
            "INSERT INTO migrations (filename, applied_by, execution_time, status) 
             VALUES (:filename, :applied_by, :execution_time, 'success')
             ON DUPLICATE KEY UPDATE 
             applied_at = CURRENT_TIMESTAMP,
             applied_by = :applied_by,
             execution_time = :execution_time,
             status = 'success',
             error_message = NULL",
            [
                'filename' => $filename,
                'applied_by' => $userId,
                'execution_time' => round($executionTime, 3)
            ]
        );
        
        $result['success'] = true;
        $result['execution_time'] = round($executionTime, 3);
        $result['rows_affected'] = $rowsAffected !== false ? $rowsAffected : 0;
        
    } catch (Exception $e) {
        $executionTime = microtime(true) - $startTime;
        $errorMessage = $e->getMessage();
        
        // Record failed migration if table exists
        if (migrations_table_exists()) {
            try {
                db_execute(
                    "INSERT INTO migrations (filename, applied_by, execution_time, status, error_message) 
                     VALUES (:filename, :applied_by, :execution_time, 'failed', :error_message)
                     ON DUPLICATE KEY UPDATE 
                     applied_at = CURRENT_TIMESTAMP,
                     applied_by = :applied_by,
                     execution_time = :execution_time,
                     status = 'failed',
                     error_message = :error_message",
                    [
                        'filename' => $filename,
                        'applied_by' => $userId,
                        'execution_time' => round($executionTime, 3),
                        'error_message' => $errorMessage
                    ]
                );
            } catch (Exception $recordError) {
                error_log('Failed to record migration error: ' . $recordError->getMessage());
            }
        }
        
        $result['error'] = $errorMessage;
        $result['execution_time'] = round($executionTime, 3);
    }
    
    return $result;
}

/**
 * Get migration status for all files
 */
function get_migration_status(): array
{
    $files = get_migration_files();
    $applied = get_applied_migrations();
    
    // Create a lookup map of applied migrations
    $appliedMap = [];
    foreach ($applied as $migration) {
        $appliedMap[$migration['filename']] = $migration;
    }
    
    $status = [];
    foreach ($files as $file) {
        $filename = $file['filename'];
        $appliedInfo = $appliedMap[$filename] ?? null;
        
        $status[] = [
            'filename' => $filename,
            'path' => $file['path'],
            'size' => $file['size'],
            'modified' => $file['modified'],
            'applied' => $appliedInfo !== null,
            'applied_at' => $appliedInfo['applied_at'] ?? null,
            'applied_by' => $appliedInfo['applied_by'] ?? null,
            'execution_time' => $appliedInfo['execution_time'] ?? null,
            'status' => $appliedInfo['status'] ?? null,
            'error_message' => $appliedInfo['error_message'] ?? null
        ];
    }
    
    return $status;
}
