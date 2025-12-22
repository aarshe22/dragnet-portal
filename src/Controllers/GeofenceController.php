<?php

namespace DragNet\Controllers;

use DragNet\Models\Geofence;

/**
 * Geofence Controller
 */
class GeofenceController extends BaseController
{
    /**
     * Show geofences page
     */
    public function index(): string
    {
        return $this->view('geofences/index');
    }
    
    /**
     * List geofences
     */
    public function list(): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $geofenceModel = new Geofence($this->app);
        $geofences = $geofenceModel->findAll(['active' => true], 'name ASC');
        
        return $this->json($geofences);
    }
    
    /**
     * Get single geofence
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $geofenceModel = new Geofence($this->app);
        $geofence = $geofenceModel->find($id);
        
        if (!$geofence) {
            return $this->json(['error' => 'Geofence not found'], 404);
        }
        
        return $this->json($geofence);
    }
    
    /**
     * Create geofence
     */
    public function create(): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $data = [
            'name' => $this->input('name'),
            'type' => $this->input('type'),
            'coordinates' => json_encode($this->input('coordinates')),
            'rules' => json_encode($this->input('rules', [])),
            'active' => true,
        ];
        
        if (empty($data['name']) || empty($data['type']) || empty($data['coordinates'])) {
            return $this->json(['error' => 'Name, type, and coordinates are required'], 400);
        }
        
        $geofenceModel = new Geofence($this->app);
        $id = $geofenceModel->create($data);
        
        return $this->json(['id' => $id, 'message' => 'Geofence created']);
    }
    
    /**
     * Update geofence
     */
    public function update(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Operator');
        
        $id = (int)($params['id'] ?? 0);
        $geofenceModel = new Geofence($this->app);
        
        if (!$geofenceModel->find($id)) {
            return $this->json(['error' => 'Geofence not found'], 404);
        }
        
        $data = [];
        if ($this->input('name') !== null) {
            $data['name'] = $this->input('name');
        }
        if ($this->input('coordinates') !== null) {
            $data['coordinates'] = json_encode($this->input('coordinates'));
        }
        if ($this->input('rules') !== null) {
            $data['rules'] = json_encode($this->input('rules'));
        }
        if ($this->input('active') !== null) {
            $data['active'] = $this->input('active') === 'true' || $this->input('active') === true;
        }
        
        if (empty($data)) {
            return $this->json(['error' => 'No data to update'], 400);
        }
        
        $geofenceModel->update($id, $data);
        
        return $this->json(['message' => 'Geofence updated']);
    }
    
    /**
     * Delete geofence
     */
    public function delete(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('Administrator');
        
        $id = (int)($params['id'] ?? 0);
        $geofenceModel = new Geofence($this->app);
        
        if (!$geofenceModel->find($id)) {
            return $this->json(['error' => 'Geofence not found'], 404);
        }
        
        $geofenceModel->delete($id);
        
        return $this->json(['message' => 'Geofence deleted']);
    }
}

