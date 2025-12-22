<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Telemetry Model
 */
class Telemetry extends BaseModel
{
    protected string $table = 'telemetry';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Override tenant scoping - telemetry is scoped by device, not directly by tenant
     */
    protected function tenantWhere(): string
    {
        return "device_id IN (SELECT id FROM devices WHERE tenant_id = :tenant_id)";
    }
    
    /**
     * Get telemetry for device within time range
     */
    public function getByDeviceAndRange(int $deviceId, string $startTime, string $endTime): array
    {
        // Verify device belongs to tenant
        $deviceModel = new Device($this->app);
        $device = $deviceModel->find($deviceId);
        if (!$device) {
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE device_id = :device_id 
                AND timestamp BETWEEN :start_time AND :end_time
                ORDER BY timestamp ASC";
        
        return $this->db->fetchAll($sql, [
            'device_id' => $deviceId,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
    }
    
    /**
     * Get latest position for all devices in tenant
     */
    public function getLatestPositions(): array
    {
        $sql = "SELECT t.*, d.tenant_id
                FROM {$this->table} t
                INNER JOIN (
                    SELECT device_id, MAX(timestamp) as max_time
                    FROM {$this->table}
                    GROUP BY device_id
                ) latest ON t.device_id = latest.device_id AND t.timestamp = latest.max_time
                INNER JOIN devices d ON t.device_id = d.id
                WHERE d.tenant_id = :tenant_id";
        
        return $this->db->fetchAll($sql, $this->tenantParams());
    }
}

