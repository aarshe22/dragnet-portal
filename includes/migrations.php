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
 * Auto-scans on each call to ensure status is current
 */
function migrations_get_status(?int $userId = null): array
{
    // Auto-scan to detect already-applied migrations
    migrations_auto_scan($userId);
    
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
        
        // Also check if it's actually applied in the database (double-check)
        $detectedApplied = migrations_detect_applied($filename);
        
        $result[] = [
            'filename' => $filename,
            'path' => $file['path'],
            'size' => $file['size'],
            'modified' => $file['modified'],
            'applied' => $isApplied || $detectedApplied,
            'detected' => $detectedApplied,
            'applied_at' => $isApplied ? $appliedMap[$filename]['applied_at'] : null,
            'applied_by' => $isApplied ? $appliedMap[$filename]['applied_by'] : null,
            'execution_time' => $isApplied ? $appliedMap[$filename]['execution_time'] : null,
            'error_message' => $isApplied ? $appliedMap[$filename]['error_message'] : null,
            'status' => $isApplied ? $appliedMap[$filename]['status'] : ($detectedApplied ? 'detected' : 'pending')
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
        
        // Execute SQL (PDO is configured with MYSQL_ATTR_MULTI_STATEMENTS)
        // Use transaction for safety
        db_begin_transaction();
        
        try {
            // Execute SET statements first (they don't need error handling)
            if (preg_match('/SET\s+FOREIGN_KEY_CHECKS\s*=\s*0/i', $sql)) {
                db()->exec("SET FOREIGN_KEY_CHECKS = 0");
            }
            
            // Split SQL into individual statements
            // Simple approach: split by semicolon followed by whitespace/newline
            // Remove comments line by line to avoid issues
            $lines = explode("\n", $sql);
            $statements = [];
            $current = '';
            
            foreach ($lines as $line) {
                // Remove inline comments
                $line = preg_replace('/--.*$/', '', $line);
                $line = rtrim($line);
                
                // Skip completely empty lines
                if ($line === '') {
                    continue;
                }
                
                $current .= $line . ' ';
                
                // Check if line ends with semicolon (statement complete)
                if (preg_match('/;\s*$/', $line)) {
                    $stmt = trim($current);
                    $current = '';
                    
                    // Remove any block comments
                    $stmt = preg_replace('/\/\*.*?\*\//s', '', $stmt);
                    $stmt = trim($stmt);
                    
                    // Skip empty statements and SET FOREIGN_KEY_CHECKS
                    if (!empty($stmt) && !preg_match('/^SET\s+FOREIGN_KEY_CHECKS/i', $stmt)) {
                        $statements[] = $stmt;
                    }
                }
            }
            
            // Handle any remaining statement
            $stmt = trim($current);
            if (!empty($stmt)) {
                $stmt = preg_replace('/\/\*.*?\*\//s', '', $stmt);
                $stmt = trim($stmt);
                if (!empty($stmt) && !preg_match('/^SET\s+FOREIGN_KEY_CHECKS/i', $stmt)) {
                    $statements[] = $stmt;
                }
            }
            
            // Execute each statement, ignoring errors for duplicate indexes/columns
            foreach ($statements as $index => $statement) {
                if (empty(trim($statement))) {
                    continue;
                }
                
                try {
                    db()->exec($statement);
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();
                    // Ignore errors for:
                    // - Duplicate key/index names (1061)
                    // - Duplicate column names (1060)
                    // - Missing columns/indexes that we're trying to drop (1054)
                    // - Tables that already exist (1050)
                    if (strpos($errorMsg, 'Duplicate key name') !== false ||
                        strpos($errorMsg, 'Duplicate column name') !== false ||
                        strpos($errorMsg, "doesn't exist") !== false ||
                        strpos($errorMsg, "already exists") !== false ||
                        strpos($errorMsg, 'Duplicate entry') !== false ||
                        preg_match('/SQLSTATE\[42S21\].*Duplicate column/', $errorMsg) ||
                        preg_match('/SQLSTATE\[42S01\].*already exists/', $errorMsg)) {
                        // Index/column/table already exists or doesn't exist - that's okay
                        continue;
                    }
                    // Re-throw with more context about which statement failed
                    $stmtPreview = substr($statement, 0, 100);
                    throw new Exception("Error executing statement #" . ($index + 1) . ": " . $errorMsg . " (Statement: " . $stmtPreview . "...)", 0, $e);
                }
            }
            
            // Restore foreign key checks
            if (preg_match('/SET\s+FOREIGN_KEY_CHECKS\s*=\s*1/i', $sql)) {
                db()->exec("SET FOREIGN_KEY_CHECKS = 1");
            }
            
            db_commit();
        } catch (Exception $e) {
            db_rollback();
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
            db()->exec("
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

/**
 * Detect if a migration was already applied by checking database schema
 */
function migrations_detect_applied(string $filename): bool
{
    $migrationsDir = __DIR__ . '/../database/migrations';
    $filePath = $migrationsDir . '/' . $filename;
    
    if (!file_exists($filePath)) {
        return false;
    }
    
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        return false;
    }
    
    $originalSql = $sql;
    
    // Normalize SQL (remove comments, normalize whitespace)
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sqlUpper = strtoupper(trim($sql));
    
    $checks = [];
    $allChecksPass = true;
    
    // Check for CREATE TABLE statements
    if (preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $sql, $matches)) {
        foreach ($matches[1] as $tableName) {
            $tableName = trim($tableName, '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.tables 
                 WHERE table_schema = DATABASE() AND table_name = :table_name",
                ['table_name' => $tableName]
            );
            $checks[] = ['type' => 'table', 'name' => $tableName, 'exists' => (bool)$exists];
            if (!$exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for ALTER TABLE ADD COLUMN statements
    if (preg_match_all('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+COLUMN\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tableName = trim($matches[1][$i], '`');
            $columnName = trim($matches[2][$i], '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                 AND table_name = :table_name 
                 AND column_name = :column_name",
                ['table_name' => $tableName, 'column_name' => $columnName]
            );
            $checks[] = ['type' => 'column', 'table' => $tableName, 'name' => $columnName, 'exists' => (bool)$exists];
            if (!$exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for ALTER TABLE MODIFY COLUMN (check if column exists with expected properties)
    if (preg_match_all('/ALTER\s+TABLE\s+`?(\w+)`?\s+MODIFY\s+COLUMN\s+`?(\w+)`?\s+([^,;]+)/i', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tableName = trim($matches[1][$i], '`');
            $columnName = trim($matches[2][$i], '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                 AND table_name = :table_name 
                 AND column_name = :column_name",
                ['table_name' => $tableName, 'column_name' => $columnName]
            );
            $checks[] = ['type' => 'column_modified', 'table' => $tableName, 'name' => $columnName, 'exists' => (bool)$exists];
            if (!$exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for ALTER TABLE DROP COLUMN statements (if column doesn't exist, migration was applied)
    if (preg_match_all('/ALTER\s+TABLE\s+`?(\w+)`?\s+DROP\s+(?:COLUMN\s+)?(?:IF\s+EXISTS\s+)?`?(\w+)`?/i', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tableName = trim($matches[1][$i], '`');
            $columnName = trim($matches[2][$i], '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                 AND table_name = :table_name 
                 AND column_name = :column_name",
                ['table_name' => $tableName, 'column_name' => $columnName]
            );
            $checks[] = ['type' => 'column_dropped', 'table' => $tableName, 'name' => $columnName, 'exists' => (bool)$exists];
            // If column doesn't exist, migration was likely applied
            if ($exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for DROP INDEX statements (if index doesn't exist, migration was applied)
    if (preg_match_all('/DROP\s+INDEX\s+(?:IF\s+EXISTS\s+)?`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $indexName = trim($matches[1][$i], '`');
            $tableName = trim($matches[2][$i], '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.statistics 
                 WHERE table_schema = DATABASE() 
                 AND table_name = :table_name 
                 AND index_name = :index_name",
                ['table_name' => $tableName, 'index_name' => $indexName]
            );
            $checks[] = ['type' => 'index_dropped', 'table' => $tableName, 'name' => $indexName, 'exists' => (bool)$exists];
            // If index doesn't exist, migration was likely applied
            if ($exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for CREATE INDEX statements
    if (preg_match_all('/CREATE\s+(?:UNIQUE\s+)?INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $indexName = trim($matches[1][$i], '`');
            $tableName = trim($matches[2][$i], '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.statistics 
                 WHERE table_schema = DATABASE() 
                 AND table_name = :table_name 
                 AND index_name = :index_name",
                ['table_name' => $tableName, 'index_name' => $indexName]
            );
            $checks[] = ['type' => 'index', 'table' => $tableName, 'name' => $indexName, 'exists' => (bool)$exists];
            if (!$exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // Check for ADD CONSTRAINT (foreign keys)
    if (preg_match_all('/ADD\s+CONSTRAINT\s+`?(\w+)`?/i', $sql, $matches)) {
        foreach ($matches[1] as $constraintName) {
            $constraintName = trim($constraintName, '`');
            $exists = db_fetch_one(
                "SELECT 1 FROM information_schema.table_constraints 
                 WHERE table_schema = DATABASE() 
                 AND constraint_name = :constraint_name",
                ['constraint_name' => $constraintName]
            );
            $checks[] = ['type' => 'constraint', 'name' => $constraintName, 'exists' => (bool)$exists];
            if (!$exists) {
                $allChecksPass = false;
            }
        }
    }
    
    // If we have specific checks and they all pass, migration was applied
    if (!empty($checks) && $allChecksPass) {
        return true;
    }
    
    // If we have checks but some failed, migration was not applied
    if (!empty($checks) && !$allChecksPass) {
        return false;
    }
    
    // If no specific checks but there are schema changes, be conservative and return false
    // (we can't determine if it was applied)
    if (preg_match('/(CREATE\s+TABLE|ALTER\s+TABLE|CREATE\s+INDEX|DROP\s+TABLE|DROP\s+COLUMN|DROP\s+INDEX|ADD\s+CONSTRAINT)/i', $sqlUpper)) {
        return false; // Can't determine, be conservative
    }
    
    // No schema changes detected, assume not applied
    return false;
}

/**
 * Scan database and mark migrations as applied if they were already applied (auto-scan, no prompts)
 */
function migrations_auto_scan(?int $userId = null): void
{
    $files = migrations_get_files();
    
    // Ensure migrations table exists
    try {
        db()->exec("
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
        // Table might already exist
    }
    
    foreach ($files as $file) {
        $filename = $file['filename'];
        
        // Check if already recorded
        $existing = null;
        try {
            $existing = db_fetch_one(
                "SELECT id, status FROM migrations WHERE filename = :filename",
                ['filename' => $filename]
            );
        } catch (Exception $e) {
            // Continue
        }
        
        // If already recorded as successful, skip
        if ($existing && $existing['status'] === 'success') {
            continue;
        }
        
        // Detect if migration was applied
        $isApplied = migrations_detect_applied($filename);
        
        if ($isApplied) {
            // Mark as applied
            if ($existing) {
                db_execute(
                    "UPDATE migrations SET 
                     applied_at = NOW(), 
                     applied_by = :user_id, 
                     status = 'success',
                     error_message = NULL
                     WHERE filename = :filename",
                    ['filename' => $filename, 'user_id' => $userId]
                );
            } else {
                db_execute(
                    "INSERT INTO migrations (filename, applied_at, applied_by, status) 
                     VALUES (:filename, NOW(), :user_id, 'success')
                     ON DUPLICATE KEY UPDATE 
                     applied_at = NOW(), 
                     applied_by = :user_id2, 
                     status = 'success',
                     error_message = NULL",
                    ['filename' => $filename, 'user_id' => $userId, 'user_id2' => $userId]
                );
            }
        }
    }
}

/**
 * Remove migration record (purge from tracking)
 */
function migrations_purge(string $filename): bool
{
    try {
        $affected = db_execute(
            "DELETE FROM migrations WHERE filename = :filename",
            ['filename' => $filename]
        );
        return $affected > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Purge all successful migrations from tracking
 */
function migrations_purge_all_successful(): int
{
    try {
        $affected = db_execute(
            "DELETE FROM migrations WHERE status = 'success'"
        );
        return $affected;
    } catch (Exception $e) {
        return 0;
    }
}

