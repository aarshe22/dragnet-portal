<?php

namespace DragNet\Controllers;

use DragNet\Models\Alert;

/**
 * Alert Controller
 */
class AlertController extends BaseController
{
    /**
     * Show alerts page
     */
    public function index(): string
    {
        return $this->view('alerts/index');
    }
    
    /**
     * List alerts (API)
     */
    public function list(): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $filters = [];
        if ($this->input('type')) {
            $filters['type'] = $this->input('type');
        }
        if ($this->input('severity')) {
            $filters['severity'] = $this->input('severity');
        }
        if ($this->input('acknowledged') !== null) {
            $filters['acknowledged'] = $this->input('acknowledged') === 'true';
        }
        
        $alertModel = new Alert($this->app);
        
        if (isset($filters['acknowledged']) && !$filters['acknowledged']) {
            $alerts = $alertModel->getUnacknowledged($filters);
        } else {
            $alerts = $alertModel->findAll($filters, 'created_at DESC');
        }
        
        return $this->json($alerts);
    }
    
    /**
     * Get single alert
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $alertModel = new Alert($this->app);
        $alert = $alertModel->find($id);
        
        if (!$alert) {
            return $this->json(['error' => 'Alert not found'], 404);
        }
        
        return $this->json($alert);
    }
    
    /**
     * Acknowledge alert
     */
    public function acknowledge(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $id = (int)($params['id'] ?? 0);
        $context = $this->app->getTenantContext();
        
        $alertModel = new Alert($this->app);
        $alert = $alertModel->find($id);
        
        if (!$alert) {
            return $this->json(['error' => 'Alert not found'], 404);
        }
        
        $alertModel->acknowledge($id, $context->getUserId());
        
        return $this->json(['message' => 'Alert acknowledged']);
    }
    
    /**
     * Assign alert
     */
    public function assign(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $id = (int)($params['id'] ?? 0);
        $userId = (int)$this->input('user_id');
        
        $alertModel = new Alert($this->app);
        $alert = $alertModel->find($id);
        
        if (!$alert) {
            return $this->json(['error' => 'Alert not found'], 404);
        }
        
        $alertModel->update($id, ['assigned_to' => $userId]);
        
        return $this->json(['message' => 'Alert assigned']);
    }
    
    /**
     * Export alerts
     */
    public function export(): void
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $filters = [];
        if ($this->input('type')) {
            $filters['type'] = $this->input('type');
        }
        if ($this->input('severity')) {
            $filters['severity'] = $this->input('severity');
        }
        
        $alertModel = new Alert($this->app);
        $alerts = $alertModel->findAll($filters, 'created_at DESC');
        
        // Simple CSV export
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="alerts_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Type', 'Severity', 'Message', 'Device', 'Created', 'Acknowledged']);
        
        foreach ($alerts as $alert) {
            fputcsv($output, [
                $alert['id'],
                $alert['type'],
                $alert['severity'],
                $alert['message'],
                $alert['device_id'],
                $alert['created_at'],
                $alert['acknowledged'] ? 'Yes' : 'No',
            ]);
        }
        
        fclose($output);
        exit;
    }
}

