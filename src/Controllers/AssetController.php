<?php

namespace DragNet\Controllers;

use DragNet\Models\Asset;

/**
 * Asset Controller
 */
class AssetController extends BaseController
{
    /**
     * Show assets list page
     */
    public function index(): string
    {
        return $this->view('assets/index');
    }
    
    /**
     * List assets (API)
     */
    public function list(): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $assetModel = new Asset($this->app);
        $assets = $assetModel->findAllWithDevices();
        
        return $this->json($assets);
    }
    
    /**
     * Show asset detail page
     */
    public function show(array $params): string
    {
        $id = (int)($params['id'] ?? 0);
        return $this->view('assets/detail', ['asset_id' => $id]);
    }
    
    /**
     * Get single asset (API)
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $assetModel = new Asset($this->app);
        $asset = $assetModel->findWithDevice($id);
        
        if (!$asset) {
            return $this->json(['error' => 'Asset not found'], 404);
        }
        
        return $this->json($asset);
    }
    
    /**
     * Create asset
     */
    public function create(): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $data = [
            'name' => $this->input('name'),
            'vehicle_id' => $this->input('vehicle_id'),
            'device_id' => $this->input('device_id') ? (int)$this->input('device_id') : null,
            'status' => $this->input('status', 'active'),
        ];
        
        if (empty($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }
        
        $assetModel = new Asset($this->app);
        $id = $assetModel->create($data);
        
        return $this->json(['id' => $id, 'message' => 'Asset created']);
    }
    
    /**
     * Update asset
     */
    public function update(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $id = (int)($params['id'] ?? 0);
        $assetModel = new Asset($this->app);
        
        if (!$assetModel->find($id)) {
            return $this->json(['error' => 'Asset not found'], 404);
        }
        
        $data = [];
        if ($this->input('name') !== null) {
            $data['name'] = $this->input('name');
        }
        if ($this->input('vehicle_id') !== null) {
            $data['vehicle_id'] = $this->input('vehicle_id');
        }
        if ($this->input('device_id') !== null) {
            $data['device_id'] = $this->input('device_id') ? (int)$this->input('device_id') : null;
        }
        if ($this->input('status') !== null) {
            $data['status'] = $this->input('status');
        }
        
        if (empty($data)) {
            return $this->json(['error' => 'No data to update'], 400);
        }
        
        $assetModel->update($id, $data);
        
        return $this->json(['message' => 'Asset updated']);
    }
    
    /**
     * Delete asset
     */
    public function delete(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $id = (int)($params['id'] ?? 0);
        $assetModel = new Asset($this->app);
        
        if (!$assetModel->find($id)) {
            return $this->json(['error' => 'Asset not found'], 404);
        }
        
        $assetModel->delete($id);
        
        return $this->json(['message' => 'Asset deleted']);
    }
}

