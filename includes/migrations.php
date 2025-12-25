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
        db_fetch_one("SELECT 1 FROM migrations LIMIT 1");
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
        $migration = db_fetch_one(
            "SELECT * FROM migrations WHERE filename = :filename",
            ['filename' => $filename]
        );
        return $migration;
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
        
        // Note: DDL statements (CREATE, ALTER, DROP) in MySQL auto-commit
        // So we can't use transactions for them. We'll execute without transaction
        // but still track the migration in the migrations table
        
        // For migration files, we need to execute raw SQL
        // Remove SQL comments first
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
        
        // Split by semicolon, but preserve empty statements for counting
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty(trim($stmt));
            }
        );
        
        $totalRows = 0;
        $hasError = false;
        $errorMessage = null;
        
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                // Execute raw SQL without parameter binding
                $rows = db_exec_raw($statement);
                if ($rows !== false) {
                    $totalRows += $rows;
                }
                // Note: DDL statements (CREATE, ALTER, DROP) return 0 rows affected
                // which is normal and doesn't indicate failure
            }
            
            // Ensure migrations table exists (for tracking)
            // If it doesn't exist and this isn't the create_migrations_table migration, try to create it
            if (!migrations_table_exists() && $filename !== 'create_migrations_table.sql') {
                // Try to create the migrations table
                try {
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
                    db_exec_raw($createTableSql);
                } catch (Exception $e) {
                    // If we can't create the table, continue anyway
                    error_log('Warning: Could not create migrations table: ' . $e->getMessage());
                }
            }
            
            // Record migration in migrations table
            if (migrations_table_exists()) {
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
            }
            
            $result['success'] = true;
            $result['execution_time'] = round(microtime(true) - $startTime, 3);
            $result['rows_affected'] = $totalRows;
            
        } catch (Exception $e) {
            $hasError = true;
            $errorMessage = $e->getMessage();
            throw $e;
        }
        
    } catch (Exception $e) {
        $executionTime = microtime(true) - $startTime;
        $errorMessage = $e->getMessage();
        
        // Record failed migration
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
                // If we can't record the error, log it
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

