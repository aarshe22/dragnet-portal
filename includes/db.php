<?php

/**
 * Database Functions (Procedural)
 * PDO-based database access with prepared statements
 */

$GLOBALS['db_connection'] = null;

/**
 * Initialize database connection
 */
function db_init(array $config): PDO
{
    global $db_connection;
    
    if ($db_connection !== null) {
        return $db_connection;
    }
    
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['name'],
        $config['charset']
    );
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db_connection = new PDO($dsn, $config['user'], $config['password'], $options);
    return $db_connection;
}

/**
 * Get database connection
 */
function db(): PDO
{
    global $db_connection;
    if ($db_connection === null) {
        throw new Exception('Database not initialized. Call db_init() first.');
    }
    return $db_connection;
}

/**
 * Execute query and return statement
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

/**
 * Fetch single row
 */
function db_fetch_one(string $sql, array $params = []): ?array
{
    $result = db_query($sql, $params)->fetch();
    return $result ?: null;
}

/**
 * Execute insert/update/delete
 */
function db_execute(string $sql, array $params = []): int
{
    $stmt = db_query($sql, $params);
    return $stmt->rowCount();
}

/**
 * Get last insert ID
 */
function db_last_insert_id(): string
{
    return db()->lastInsertId();
}

/**
 * Begin transaction
 */
function db_begin_transaction(): bool
{
    return db()->beginTransaction();
}

/**
 * Commit transaction
 */
function db_commit(): bool
{
    return db()->commit();
}

/**
 * Rollback transaction
 */
function db_rollback(): bool
{
    return db()->rollBack();
}

/**
 * Execute raw SQL (for migrations, DDL statements, etc.)
 * Use this for SQL that doesn't need parameter binding
 */
function db_exec_raw(string $sql): int
{
    return db()->exec($sql);
}

