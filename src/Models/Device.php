<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Device Model
 */
class Device extends BaseModel
{
    protected string $table = 'devices';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Get latest telemetry for device
     */
    public function getLatestTelemetry(int $deviceId): ?array
    {
        $sql = "SELECT * FROM telemetry 
                WHERE device_id = :device_id 
                ORDER BY timestamp DESC 
                LIMIT 1";
        
        return $this->db->fetchOne($sql, ['device_id' => $deviceId]);
    }
    
    /**
     * Get devices with current status
     */
    public function findAllWithStatus(): array
    {
        $sql = "SELECT d.*, 
                       (SELECT lat FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lat,
                       (SELECT lon FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as lon,
                       (SELECT speed FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as speed,
                       (SELECT timestamp FROM telemetry WHERE device_id = d.id ORDER BY timestamp DESC LIMIT 1) as last_position_time
                FROM {$this->table} d
                WHERE {$this->tenantWhere()}
                ORDER BY d.last_checkin DESC";
        
        return $this->db->fetchAll($sql, $this->tenantParams());
    }
    
    /**
     * Update device status based on last check-in
     */
    public function updateStatus(int $deviceId): void
    {
        $device = $this->find($deviceId);
        if (!$device) {
            return;
        }
        
        $lastCheckin = strtotime($device['last_checkin'] ?? '1970-01-01');
        $now = time();
        $minutesSinceCheckin = ($now - $lastCheckin) / 60;
        
        $status = 'online';
        if ($minutesSinceCheckin > 15) {
            $status = 'offline';
        }
        
        $this->update($deviceId, ['status' => $status]);
    }
}

