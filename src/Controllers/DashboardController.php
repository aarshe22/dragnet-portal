<?php

namespace DragNet\Controllers;

use DragNet\Models\Device;
use DragNet\Models\Asset;
use DragNet\Models\Alert;

/**
 * Dashboard Controller
 */
class DashboardController extends BaseController
{
    /**
     * Show dashboard page
     */
    public function index(): string
    {
        return $this->view('dashboard/index');
    }
    
    /**
     * Get dashboard widgets data
     */
    public function getWidgets(): array
    {
        $this->requireTenant();
        
        $deviceModel = new Device($this->app);
        $assetModel = new Asset($this->app);
        $alertModel = new Alert($this->app);
        
        // Total devices
        $totalDevices = $deviceModel->count();
        
        // Online vs offline
        $onlineDevices = $deviceModel->count(['status' => 'online']);
        $offlineDevices = $deviceModel->count(['status' => 'offline']);
        
        // Total assets
        $totalAssets = $assetModel->count();
        
        // Active alerts
        $activeAlerts = $alertModel->count(['acknowledged' => false]);
        $criticalAlerts = $alertModel->count(['acknowledged' => false, 'severity' => 'critical']);
        
        // Device status breakdown
        $devices = $deviceModel->findAll();
        $moving = 0;
        $idle = 0;
        $parked = 0;
        
        foreach ($devices as $device) {
            $telemetry = $deviceModel->getLatestTelemetry($device['id']);
            if ($telemetry) {
                $speed = (float)($telemetry['speed'] ?? 0);
                if ($speed > 5) {
                    $moving++;
                } elseif ($speed > 0) {
                    $idle++;
                } else {
                    $parked++;
                }
            } else {
                $parked++;
            }
        }
        
        return $this->json([
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'offline_devices' => $offlineDevices,
            'total_assets' => $totalAssets,
            'active_alerts' => $activeAlerts,
            'critical_alerts' => $criticalAlerts,
            'moving' => $moving,
            'idle' => $idle,
            'parked' => $parked,
        ]);
    }
}

