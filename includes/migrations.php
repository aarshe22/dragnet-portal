<?php

/**
 * Migration Management Functions
 * Read, check, and apply database migrations
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Get all migration files from the migrations directory
 */
function migrations_get_files(): array
{
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = [];
    
    if (!is_dir($migrationsDir)) {
        return $files;
    }
    
    $handle = opendir($migrationsDir);
    if (!$handle) {
        return $files;
    }
    
    while (($file = readdir($handle)) !== false) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $migrationsDir . '/' . $file;
            $files[] = [
                'filename' => $file,
                'path' => $filePath,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath)
            ];
        }
    }
    
    closedir($handle);
    
    // Sort by filename (oldest to newest)
    usort($files, function($a, $b) {
        return strcmp($a['filename'], $b['filename']);
    });
    
    return $files;
}

/**
 * Get applied migrations from database
 */
function migrations_get_applied(): array
{
    try {
        // Check if migrations table exists by trying to query it
        return db_fetch_all(
            "SELECT filename, applied_at, applied_by, execution_time, error_message, status 
             FROM migrations 
             ORDER BY applied_at ASC"
        );
    } catch (Exception $e) {
        // Table doesn't exist yet, return empty array
        return [];
    }
}

/**
 * Get migration status (all files with their applied status)
 */
function migrations_get_status(): array
{
    $files = migrations_get_files();
    $applied = migrations_get_applied();
    
    // Create a map of applied migrations
    $appliedMap = [];
    foreach ($applied as $migration) {
        $appliedMap[$migration['filename']] = $migration;
    }
    
    // Combine files with their status
    $result = [];
    foreach ($files as $file) {
        $filename = $file['filename'];
        $isApplied = isset($appliedMap[$filename]);
        
        $result[] = [
            'filename' => $filename,
            'path' => $file['path'],
            'size' => $file['size'],
            'modified' => $file['modified'],
            'applied' => $isApplied,
            'applied_at' => $isApplied ? $appliedMap[$filename]['applied_at'] : null,
            'applied_by' => $isApplied ? $appliedMap[$filename]['applied_by'] : null,
            'execution_time' => $isApplied ? $appliedMap[$filename]['execution_time'] : null,
            'error_message' => $isApplied ? $appliedMap[$filename]['error_message'] : null,
            'status' => $isApplied ? $appliedMap[$filename]['status'] : 'pending'
        ];
    }
    
    return $result;
}

/**
 * Apply a migration
 */
function migrations_apply(string $filename, ?int $userId = null): array
{
    $migrationsDir = __DIR__ . '/../database/migrations';
    $filePath = $migrationsDir . '/' . $filename;
    
    if (!file_exists($filePath)) {
        return ['success' => false, 'error' => 'Migration file not found'];
    }
    
    // Check if already applied (only if migrations table exists)
    $existing = null;
    try {
        $existing = db_fetch_one(
            "SELECT id, status FROM migrations WHERE filename = :filename",
            ['filename' => $filename]
        );
    } catch (Exception $e) {
        // Migrations table doesn't exist yet, that's okay
    }
    
    if ($existing && $existing['status'] === 'success') {
        return ['success' => false, 'error' => 'Migration already applied successfully'];
    }
    
    $startTime = microtime(true);
    $error = null;
    $status = 'success';
    
    try {
        // Read SQL file
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception('Could not read migration file');
        }
        
        // Split by semicolons (handle multiple statements)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        // Execute each statement
        db_get_pdo()->beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                if (empty(trim($statement))) {
                    continue;
                }
                db_get_pdo()->exec($statement);
            }
            
            db_get_pdo()->commit();
        } catch (Exception $e) {
            db_get_pdo()->rollBack();
            throw $e;
        }
        
        $executionTime = microtime(true) - $startTime;
        
        // Record migration
        if ($existing) {
            // Update existing record
            db_execute(
                "UPDATE migrations SET 
                 applied_at = NOW(), 
                 applied_by = :user_id, 
                 execution_time = :execution_time, 
                 error_message = NULL, 
                 status = :status 
                 WHERE filename = :filename",
                [
                    'filename' => $filename,
                    'user_id' => $userId,
                    'execution_time' => round($executionTime, 3),
                    'status' => $status
                ]
            );
        } else {
            // Insert new record
            db_execute(
                "INSERT INTO migrations (filename, applied_at, applied_by, execution_time, status) 
                 VALUES (:filename, NOW(), :user_id, :execution_time, :status)",
                [
                    'filename' => $filename,
                    'user_id' => $userId,
                    'execution_time' => round($executionTime, 3),
                    'status' => $status
                ]
            );
        }
        
        return [
            'success' => true,
            'message' => 'Migration applied successfully',
            'execution_time' => round($executionTime, 3)
        ];
        
    } catch (Exception $e) {
        $executionTime = microtime(true) - $startTime;
        $error = $e->getMessage();
        $status = 'failed';
        
        // Ensure migrations table exists
        try {
            db_get_pdo()->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL UNIQUE,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    applied_by INT UNSIGNED NULL,
                    execution_time DECIMAL(10, 3) NULL,
                    error_message TEXT NULL,
                    status ENUM('success', 'failed', 'partial') DEFAULT 'success',
                    INDEX idx_filename (filename),
                    INDEX idx_applied_at (applied_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            // Table might already exist, continue
        }
        
        // Record failed migration
        if ($existing) {
            db_execute(
                "UPDATE migrations SET 
                 applied_at = NOW(), 
                 applied_by = :user_id, 
                 execution_time = :execution_time, 
                 error_message = :error_message, 
                 status = :status 
                 WHERE filename = :filename",
                [
                    'filename' => $filename,
                    'user_id' => $userId,
                    'execution_time' => round($executionTime, 3),
                    'error_message' => $error,
                    'status' => $status
                ]
            );
        } else {
            db_execute(
                "INSERT INTO migrations (filename, applied_at, applied_by, execution_time, error_message, status) 
                 VALUES (:filename, NOW(), :user_id, :execution_time, :error_message, :status)",
                [
                    'filename' => $filename,
                    'user_id' => $userId,
                    'execution_time' => round($executionTime, 3),
                    'error_message' => $error,
                    'status' => $status
                ]
            );
        }
        
        return [
            'success' => false,
            'error' => $error,
            'execution_time' => round($executionTime, 3)
        ];
    }
}

/**
 * Get migration file content (for preview)
 */
function migrations_get_content(string $filename): ?string
{
    $migrationsDir = __DIR__ . '/../database/migrations';
    $filePath = $migrationsDir . '/' . $filename;
    
    if (!file_exists($filePath)) {
        return null;
    }
    
    return file_get_contents($filePath);
}

