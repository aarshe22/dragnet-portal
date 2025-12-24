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
    
    /**
     * Find device by IMEI
     */
    public function findByImei(string $imei, ?int $tenantId = null): ?array
    {
        $where = ["imei = :imei"];
        $params = ['imei' => $imei];
        
        if ($tenantId !== null) {
            $where[] = "tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        } elseif ($this->tenantId !== null) {
            $where[] = $this->tenantWhere();
            $params = array_merge($params, $this->tenantParams());
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Register Teltonika device by IMEI
     */
    public function registerTeltonika(string $imei, int $tenantId, string $deviceUid = null): array
    {
        // Check if already exists
        $existing = $this->findByImei($imei);
        if ($existing) {
            return $existing;
        }
        
        // Create new device
        $deviceUid = $deviceUid ?? 'TELTONIKA_' . $imei;
        
        $deviceId = $this->create([
            'tenant_id' => $tenantId,
            'device_uid' => $deviceUid,
            'imei' => $imei,
            'device_type' => 'teltonika_fmm13a',
            'protocol' => 'http',
            'status' => 'offline',
        ]);
        
        return $this->find($deviceId);
    }
}

