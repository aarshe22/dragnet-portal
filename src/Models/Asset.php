<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Asset Model
 */
class Asset extends BaseModel
{
    protected string $table = 'assets';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Get asset with device information
     */
    public function findWithDevice(int $id): ?array
    {
        $sql = "SELECT a.*, d.device_uid, d.imei, d.status as device_status, 
                       d.last_checkin, d.battery_level, d.signal_strength
                FROM {$this->table} a
                LEFT JOIN devices d ON a.device_id = d.id
                WHERE a.id = :id AND a.{$this->tenantWhere()}";
        
        $params = array_merge(['id' => $id], $this->tenantParams());
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Get all assets with device status
     */
    public function findAllWithDevices(array $conditions = []): array
    {
        $where = ["a.{$this->tenantWhere()}"];
        $params = $this->tenantParams();
        
        foreach ($conditions as $key => $value) {
            $where[] = "a.{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "SELECT a.*, d.device_uid, d.status as device_status, d.last_checkin
                FROM {$this->table} a
                LEFT JOIN devices d ON a.device_id = d.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.name";
        
        return $this->db->fetchAll($sql, $params);
    }
}

