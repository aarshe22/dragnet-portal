<?php

namespace DragNet\Controllers;

use DragNet\Models\Device;
use DragNet\Core\Database;

/**
 * Video Controller
 */
class VideoController extends BaseController
{
    /**
     * Show video review page
     */
    public function index(array $params): string
    {
        $assetId = (int)($params['id'] ?? 0);
        return $this->view('video/index', ['asset_id' => $assetId]);
    }
    
    /**
     * List video segments for asset
     */
    public function list(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $assetId = (int)($params['id'] ?? 0);
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        
        // Get device for asset
        $assetModel = new \DragNet\Models\Asset($this->app);
        $asset = $assetModel->find($assetId);
        
        if (!$asset || !$asset['device_id']) {
            return $this->json([]);
        }
        
        $db = $this->app->getDatabase();
        $where = ["device_id = :device_id"];
        $queryParams = ['device_id' => $asset['device_id']];
        
        if ($startDate) {
            $where[] = "start_time >= :start_date";
            $queryParams['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "end_time <= :end_date";
            $queryParams['end_date'] = $endDate;
        }
        
        $sql = "SELECT * FROM video_segments 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY start_time DESC";
        
        $segments = $db->fetchAll($sql, $queryParams);
        
        return $this->json($segments);
    }
    
    /**
     * Get video segment metadata
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $db = $this->app->getDatabase();
        
        $sql = "SELECT vs.*, d.tenant_id
                FROM video_segments vs
                INNER JOIN devices d ON vs.device_id = d.id
                WHERE vs.id = :id AND d.tenant_id = :tenant_id";
        
        $segment = $db->fetchOne($sql, [
            'id' => $id,
            'tenant_id' => $this->requireTenant()
        ]);
        
        if (!$segment) {
            return $this->json(['error' => 'Video segment not found'], 404);
        }
        
        return $this->json($segment);
    }
    
    /**
     * Stream video segment
     */
    public function stream(array $params): void
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $db = $this->app->getDatabase();
        
        $sql = "SELECT vs.*, d.tenant_id
                FROM video_segments vs
                INNER JOIN devices d ON vs.device_id = d.id
                WHERE vs.id = :id AND d.tenant_id = :tenant_id";
        
        $segment = $db->fetchOne($sql, [
            'id' => $id,
            'tenant_id' => $this->requireTenant()
        ]);
        
        if (!$segment) {
            http_response_code(404);
            exit;
        }
        
        $filePath = $segment['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }
        
        // Log video access for audit
        // (audit logging would go here)
        
        header('Content-Type: video/mp4');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    /**
     * Download video segment
     */
    public function download(array $params): void
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $db = $this->app->getDatabase();
        
        $sql = "SELECT vs.*, d.tenant_id
                FROM video_segments vs
                INNER JOIN devices d ON vs.device_id = d.id
                WHERE vs.id = :id AND d.tenant_id = :tenant_id";
        
        $segment = $db->fetchOne($sql, [
            'id' => $id,
            'tenant_id' => $this->requireTenant()
        ]);
        
        if (!$segment) {
            http_response_code(404);
            exit;
        }
        
        $filePath = $segment['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }
        
        // Log video download for audit
        // (audit logging would go here)
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="video_' . $id . '.mp4"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

