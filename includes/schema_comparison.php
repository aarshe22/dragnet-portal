<?php

/**
 * Schema Comparison Functions
 * Compare live database with schema.sql seed file
 */

require_once __DIR__ . '/db.php';

/**
 * Get current database schema
 */
function schema_get_current(): array
{
    $tables = db_fetch_all(
        "SELECT table_name 
         FROM information_schema.tables 
         WHERE table_schema = DATABASE() 
         AND table_type = 'BASE TABLE'
         ORDER BY table_name"
    );
    
    $schema = [];
    foreach ($tables as $table) {
        $tableName = $table['table_name'];
        
        // Get columns
        $columns = db_fetch_all(
            "SELECT column_name, data_type, is_nullable, column_default, column_type, extra
             FROM information_schema.columns 
             WHERE table_schema = DATABASE() 
             AND table_name = :table_name
             ORDER BY ordinal_position",
            ['table_name' => $tableName]
        );
        
        // Get indexes
        $indexes = db_fetch_all(
            "SELECT index_name, column_name, non_unique, index_type
             FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = :table_name
             ORDER BY index_name, seq_in_index",
            ['table_name' => $tableName]
        );
        
        // Get constraints
        $constraints = db_fetch_all(
            "SELECT tc.constraint_name, tc.constraint_type, kcu.column_name, kcu.referenced_table_name, kcu.referenced_column_name
             FROM information_schema.table_constraints tc
             LEFT JOIN information_schema.key_column_usage kcu 
                ON tc.constraint_name = kcu.constraint_name 
                AND tc.table_schema = kcu.table_schema
                AND tc.table_name = kcu.table_name
             WHERE tc.table_schema = DATABASE() 
             AND tc.table_name = :table_name
             ORDER BY tc.constraint_name, kcu.ordinal_position",
            ['table_name' => $tableName]
        );
        
        $schema[$tableName] = [
            'columns' => $columns,
            'indexes' => $indexes,
            'constraints' => $constraints
        ];
    }
    
    return $schema;
}

/**
 * Parse schema.sql file to extract expected schema
 */
function schema_get_expected(): array
{
    $schemaFile = __DIR__ . '/../database/schema.sql';
    
    if (!file_exists($schemaFile)) {
        return [];
    }
    
    $content = file_get_contents($schemaFile);
    if ($content === false) {
        return [];
    }
    
    $schema = [];
    
    // Extract CREATE TABLE statements
    if (preg_match_all('/CREATE TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*ENGINE/i', $content, $tableMatches, PREG_SET_ORDER)) {
        foreach ($tableMatches as $match) {
            $tableName = $match[1];
            $tableDef = $match[2];
            
            // Extract columns
            $columns = [];
            if (preg_match_all('/`(\w+)`\s+([^,`]+)/', $tableDef, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $colMatch) {
                    $columns[] = [
                        'name' => $colMatch[1],
                        'definition' => trim($colMatch[2])
                    ];
                }
            }
            
            $schema[$tableName] = [
                'columns' => $columns,
                'definition' => $tableDef,
                'indexes' => [] // Initialize indexes array
            ];
        }
    }
    
    // Extract ALTER TABLE ADD INDEX statements
    if (preg_match_all('/ALTER TABLE\s+`?(\w+)`?\s+ADD\s+(?:UNIQUE\s+)?(?:KEY|INDEX)\s+`?(\w+)`?\s+\(([^)]+)\)/i', $content, $indexMatches, PREG_SET_ORDER)) {
        foreach ($indexMatches as $match) {
            $tableName = $match[1];
            $indexName = $match[2];
            if (!isset($schema[$tableName]['indexes'])) {
                $schema[$tableName]['indexes'] = [];
            }
            $schema[$tableName]['indexes'][] = [
                'name' => $indexName,
                'columns' => $match[3]
            ];
        }
    }
    
    return $schema;
}

/**
 * Compare current database schema with expected schema from schema.sql
 */
function schema_compare(): array
{
    $current = schema_get_current();
    $expected = schema_get_expected();
    
    $differences = [
        'missing_tables' => [],
        'extra_tables' => [],
        'missing_columns' => [],
        'extra_columns' => [],
        'missing_indexes' => [],
        'extra_indexes' => [],
        'matches' => true
    ];
    
    // Check for missing tables
    foreach ($expected as $tableName => $tableDef) {
        if (!isset($current[$tableName])) {
            $differences['missing_tables'][] = $tableName;
            $differences['matches'] = false;
        }
    }
    
    // Check for extra tables
    foreach ($current as $tableName => $tableDef) {
        if (!isset($expected[$tableName])) {
            $differences['extra_tables'][] = $tableName;
            $differences['matches'] = false;
        }
    }
    
    // Check columns for existing tables
    foreach ($expected as $tableName => $tableDef) {
        if (!isset($current[$tableName])) {
            continue; // Table doesn't exist, already reported
        }
        
        // Ensure columns array exists
        if (!isset($tableDef['columns']) || !is_array($tableDef['columns'])) {
            continue; // No column definition in expected schema
        }
        
        if (!isset($current[$tableName]['columns']) || !is_array($current[$tableName]['columns'])) {
            continue; // No columns in current schema (shouldn't happen, but be safe)
        }
        
        $expectedColumns = array_column($tableDef['columns'], 'name');
        $currentColumns = array_column($current[$tableName]['columns'], 'column_name');
        
        foreach ($expectedColumns as $colName) {
            if (!in_array($colName, $currentColumns)) {
                $differences['missing_columns'][] = [
                    'table' => $tableName,
                    'column' => $colName
                ];
                $differences['matches'] = false;
            }
        }
        
        // Check for extra columns (columns in DB but not in schema.sql)
        foreach ($currentColumns as $colName) {
            if (!in_array($colName, $expectedColumns)) {
                $differences['extra_columns'][] = [
                    'table' => $tableName,
                    'column' => $colName
                ];
                $differences['matches'] = false;
            }
        }
    }
    
    return $differences;
}

/**
 * Update schema.sql to match current database
 */
function schema_update_seed_file(): bool
{
    $schemaFile = __DIR__ . '/../database/schema.sql';
    $backupFile = $schemaFile . '.backup.' . date('Y-m-d_His');
    
    // Create backup
    if (file_exists($schemaFile)) {
        copy($schemaFile, $backupFile);
    }
    
    // Generate new schema.sql from current database
    $schema = schema_get_current();
    
    $output = "-- Dragnet Intelematics Database Schema\n";
    $output .= "-- Auto-generated from live database\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
    $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
    $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
    
    // Output table structures
    foreach ($schema as $tableName => $tableDef) {
        $output .= "-- --------------------------------------------------------\n\n";
        $output .= "--\n";
        $output .= "-- Table structure for table `{$tableName}`\n";
        $output .= "--\n\n";
        $output .= "CREATE TABLE `{$tableName}` (\n";
        
        // Output columns
        $columnDefs = [];
        foreach ($tableDef['columns'] as $col) {
            $def = "  `{$col['column_name']}` {$col['column_type']}";
            if ($col['is_nullable'] === 'NO') {
                $def .= " NOT NULL";
            }
            if ($col['column_default'] !== null) {
                if ($col['column_default'] === 'CURRENT_TIMESTAMP' || strpos($col['column_default'], 'CURRENT_TIMESTAMP') !== false) {
                    $def .= " DEFAULT " . $col['column_default'];
                } else {
                    $def .= " DEFAULT " . (is_numeric($col['column_default']) ? $col['column_default'] : "'" . addslashes($col['column_default']) . "'");
                }
            }
            if ($col['extra']) {
                $def .= " " . $col['extra'];
            }
            $columnDefs[] = $def;
        }
        
        $output .= implode(",\n", $columnDefs) . "\n";
        $output .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    }
    
    // Output indexes
    $output .= "-- --------------------------------------------------------\n\n";
    $output .= "--\n";
    $output .= "-- Indexes for dumped tables\n";
    $output .= "--\n\n";
    
    foreach ($schema as $tableName => $tableDef) {
        if (!empty($tableDef['indexes'])) {
            $output .= "--\n";
            $output .= "-- Indexes for table `{$tableName}`\n";
            $output .= "--\n";
            
            // Group indexes by name
            $indexGroups = [];
            foreach ($tableDef['indexes'] as $idx) {
                $idxName = $idx['index_name'];
                if (!isset($indexGroups[$idxName])) {
                    $indexGroups[$idxName] = [];
                }
                $indexGroups[$idxName][] = $idx;
            }
            
            foreach ($indexGroups as $idxName => $idxCols) {
                $isPrimary = ($idxName === 'PRIMARY');
                $isUnique = ($idxCols[0]['non_unique'] == 0 && !$isPrimary);
                
                $cols = array_map(function($col) {
                    return "`{$col['column_name']}`";
                }, $idxCols);
                
                if ($isPrimary) {
                    $output .= "ALTER TABLE `{$tableName}`\n";
                    $output .= "  ADD PRIMARY KEY (" . implode(',', $cols) . ");\n";
                } elseif ($isUnique) {
                    $output .= "ALTER TABLE `{$tableName}`\n";
                    $output .= "  ADD UNIQUE KEY `{$idxName}` (" . implode(',', $cols) . ");\n";
                } else {
                    $output .= "ALTER TABLE `{$tableName}`\n";
                    $output .= "  ADD KEY `{$idxName}` (" . implode(',', $cols) . ");\n";
                }
            }
            $output .= "\n";
        }
    }
    
    // Output AUTO_INCREMENT
    $output .= "-- --------------------------------------------------------\n\n";
    $output .= "--\n";
    $output .= "-- AUTO_INCREMENT for dumped tables\n";
    $output .= "--\n\n";
    
    foreach ($schema as $tableName => $tableDef) {
        foreach ($tableDef['columns'] as $col) {
            if (strpos($col['extra'], 'auto_increment') !== false) {
                $output .= "--\n";
                $output .= "-- AUTO_INCREMENT for table `{$tableName}`\n";
                $output .= "--\n";
                $output .= "ALTER TABLE `{$tableName}`\n";
                $output .= "  MODIFY `{$col['column_name']}` {$col['column_type']} NOT NULL AUTO_INCREMENT;\n\n";
                break; // Only one auto_increment per table
            }
        }
    }
    
    // Output foreign keys
    $output .= "-- --------------------------------------------------------\n\n";
    $output .= "--\n";
    $output .= "-- Constraints for dumped tables\n";
    $output .= "--\n\n";
    
    foreach ($schema as $tableName => $tableDef) {
        if (!empty($tableDef['constraints'])) {
            $output .= "--\n";
            $output .= "-- Constraints for table `{$tableName}`\n";
            $output .= "--\n";
            
            foreach ($tableDef['constraints'] as $constraint) {
                if ($constraint['constraint_type'] === 'FOREIGN KEY') {
                    $output .= "ALTER TABLE `{$tableName}`\n";
                    $output .= "  ADD CONSTRAINT `{$constraint['constraint_name']}` FOREIGN KEY (`{$constraint['column_name']}`) REFERENCES `{$constraint['referenced_table_name']}` (`{$constraint['referenced_column_name']}`)";
                    
                    // Get ON DELETE/UPDATE actions from information_schema
                    $fkInfo = db_fetch_one(
                        "SELECT DELETE_RULE, UPDATE_RULE 
                         FROM information_schema.REFERENTIAL_CONSTRAINTS 
                         WHERE CONSTRAINT_SCHEMA = DATABASE() 
                         AND CONSTRAINT_NAME = :constraint_name",
                        ['constraint_name' => $constraint['constraint_name']]
                    );
                    
                    if ($fkInfo) {
                        if ($fkInfo['DELETE_RULE'] !== 'RESTRICT') {
                            $output .= " ON DELETE " . strtoupper($fkInfo['DELETE_RULE']);
                        }
                        if ($fkInfo['UPDATE_RULE'] !== 'RESTRICT') {
                            $output .= " ON UPDATE " . strtoupper($fkInfo['UPDATE_RULE']);
                        }
                    }
                    
                    $output .= ";\n";
                }
            }
            $output .= "\n";
        }
    }
    
    $output .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
    $output .= "COMMIT;\n\n";
    $output .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
    $output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
    $output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
    
    return file_put_contents($schemaFile, $output) !== false;
}

