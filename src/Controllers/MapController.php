<?php

namespace DragNet\Controllers;

use DragNet\Models\Device;
use DragNet\Models\Telemetry;
use DragNet\Models\Geofence;

/**
 * Live Map Controller
 */
class MapController extends BaseController
{
    /**
     * Show map page
     */
    public function index(): string
    {
        return $this->view('map/index');
    }
    
    /**
     * Get devices for map display
     */
    public function getDevices(): array
    {
        $this->requireTenant();
        
        $deviceModel = new Device($this->app);
        $devices = $deviceModel->findAllWithStatus();
        
        $result = [];
        foreach ($devices as $device) {
            $result[] = [
                'id' => $device['id'],
                'device_uid' => $device['device_uid'],
                'asset_id' => $device['asset_id'],
                'status' => $device['status'],
                'lat' => $device['lat'] ? (float)$device['lat'] : null,
                'lon' => $device['lon'] ? (float)$device['lon'] : null,
                'speed' => $device['speed'] ? (float)$device['speed'] : null,
                'heading' => $device['heading'] ?? null,
                'last_checkin' => $device['last_checkin'],
                'battery_level' => $device['battery_level'],
                'signal_strength' => $device['signal_strength'],
            ];
        }
        
        return $this->json($result);
    }
    
    /**
     * Get geofences for map display
     */
    public function getGeofences(): array
    {
        $this->requireTenant();
        
        $geofenceModel = new Geofence($this->app);
        $geofences = $geofenceModel->findAll(['active' => true]);
        
        $result = [];
        foreach ($geofences as $geofence) {
            $result[] = [
                'id' => $geofence['id'],
                'name' => $geofence['name'],
                'type' => $geofence['type'],
                'coordinates' => json_decode($geofence['coordinates'], true),
                'rules' => json_decode($geofence['rules'] ?? '{}', true),
            ];
        }
        
        return $this->json($result);
    }
}

