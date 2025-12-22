<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Alert Model
 */
class Alert extends BaseModel
{
    protected string $table = 'alerts';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Get unacknowledged alerts
     */
    public function getUnacknowledged(array $filters = []): array
    {
        $where = [$this->tenantWhere(), "acknowledged = 0"];
        $params = $this->tenantParams();
        
        if (isset($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (isset($filters['severity'])) {
            $where[] = "severity = :severity";
            $params['severity'] = $filters['severity'];
        }
        
        $sql = "SELECT a.*, d.device_uid, d.imei, ast.name as asset_name
                FROM {$this->table} a
                INNER JOIN devices d ON a.device_id = d.id
                LEFT JOIN assets ast ON d.asset_id = ast.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Acknowledge alert
     */
    public function acknowledge(int $alertId, int $userId): bool
    {
        return $this->update($alertId, [
            'acknowledged' => true,
            'acknowledged_by' => $userId,
            'acknowledged_at' => date('Y-m-d H:i:s')
        ]);
    }
}

