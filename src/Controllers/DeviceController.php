<?php

namespace DragNet\Controllers;

use DragNet\Models\Device;
use DragNet\Models\Telemetry;

/**
 * Device Controller
 */
class DeviceController extends BaseController
{
    /**
     * Show devices list page
     */
    public function index(): string
    {
        return $this->view('devices/index');
    }
    
    /**
     * List devices (API)
     */
    public function list(): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $deviceModel = new Device($this->app);
        $devices = $deviceModel->findAll();
        
        return $this->json($devices);
    }
    
    /**
     * Show device detail page
     */
    public function show(array $params): string
    {
        $id = (int)($params['id'] ?? 0);
        return $this->view('devices/detail', ['device_id' => $id]);
    }
    
    /**
     * Get single device (API)
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $deviceModel = new Device($this->app);
        $device = $deviceModel->find($id);
        
        if (!$device) {
            return $this->json(['error' => 'Device not found'], 404);
        }
        
        $device['latest_telemetry'] = $deviceModel->getLatestTelemetry($id);
        
        return $this->json($device);
    }
    
    /**
     * Get device telemetry
     */
    public function getTelemetry(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $startTime = $this->input('start_time', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $endTime = $this->input('end_time', date('Y-m-d H:i:s'));
        
        $telemetryModel = new Telemetry($this->app);
        $telemetry = $telemetryModel->getByDeviceAndRange($id, $startTime, $endTime);
        
        return $this->json($telemetry);
    }
    
    /**
     * Get device status
     */
    public function getStatus(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $deviceModel = new Device($this->app);
        $device = $deviceModel->find($id);
        
        if (!$device) {
            return $this->json(['error' => 'Device not found'], 404);
        }
        
        $deviceModel->updateStatus($id);
        $device = $deviceModel->find($id);
        
        return $this->json([
            'status' => $device['status'],
            'last_checkin' => $device['last_checkin'],
            'battery_level' => $device['battery_level'],
            'signal_strength' => $device['signal_strength'],
        ]);
    }
}

