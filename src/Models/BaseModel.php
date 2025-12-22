<?php

namespace DragNet\Models;

use DragNet\Core\Database;
use DragNet\Core\Application;

/**
 * Base Model
 * 
 * Provides common database operations with tenant scoping.
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table;
    protected ?int $tenantId = null;
    
    public function __construct(Application $app)
    {
        $this->db = $app->getDatabase();
        $this->tenantId = $app->getTenantId();
    }
    
    /**
     * Set tenant ID explicitly (for admin operations)
     */
    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
    
    /**
     * Build tenant-scoped WHERE clause
     */
    protected function tenantWhere(): string
    {
        if ($this->tenantId === null) {
            throw new \Exception('Tenant ID required');
        }
        return "tenant_id = :tenant_id";
    }
    
    /**
     * Get tenant-scoped parameters
     */
    protected function tenantParams(): array
    {
        if ($this->tenantId === null) {
            throw new \Exception('Tenant ID required');
        }
        return ['tenant_id' => $this->tenantId];
    }
    
    /**
     * Find by ID (tenant-scoped)
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND {$this->tenantWhere()}";
        $params = array_merge(['id' => $id], $this->tenantParams());
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Find all (tenant-scoped)
     */
    public function findAll(array $conditions = [], string $orderBy = 'id DESC', int $limit = null, int $offset = 0): array
    {
        $where = [$this->tenantWhere()];
        $params = $this->tenantParams();
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY {$orderBy}";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int
    {
        if ($this->tenantId !== null) {
            $data['tenant_id'] = $this->tenantId;
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":{$f}", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->query($sql, $data);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $fields = array_keys($data);
        $set = array_map(fn($f) => "{$f} = :{$f}", $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . 
               " WHERE id = :id AND {$this->tenantWhere()}";
        
        $params = array_merge($data, ['id' => $id], $this->tenantParams());
        $affected = $this->db->execute($sql, $params);
        return $affected > 0;
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND {$this->tenantWhere()}";
        $params = array_merge(['id' => $id], $this->tenantParams());
        $affected = $this->db->execute($sql, $params);
        return $affected > 0;
    }
    
    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $where = [$this->tenantWhere()];
        $params = $this->tenantParams();
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $where);
        $result = $this->db->fetchOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }
}

