<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Trip Model
 */
class Trip extends BaseModel
{
    protected string $table = 'trips';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Override tenant scoping - trips are scoped by device
     */
    protected function tenantWhere(): string
    {
        return "device_id IN (SELECT id FROM devices WHERE tenant_id = :tenant_id)";
    }
    
    /**
     * Get trips for asset
     */
    public function getByAsset(int $assetId, string $startDate = null, string $endDate = null): array
    {
        $where = ["asset_id = :asset_id"];
        $params = ['asset_id' => $assetId];
        
        if ($startDate) {
            $where[] = "start_time >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "end_time <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY start_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}

