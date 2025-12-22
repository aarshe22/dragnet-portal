<?php

namespace DragNet\Controllers;

use DragNet\Models\Trip;
use DragNet\Models\Asset;
use DragNet\Models\Telemetry;

/**
 * Trip Controller
 */
class TripController extends BaseController
{
    /**
     * Show trips page for asset
     */
    public function index(array $params): string
    {
        $assetId = (int)($params['id'] ?? 0);
        return $this->view('trips/index', ['asset_id' => $assetId]);
    }
    
    /**
     * List trips for asset
     */
    public function list(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $assetId = (int)($params['id'] ?? 0);
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        
        $tripModel = new Trip($this->app);
        $trips = $tripModel->getByAsset($assetId, $startDate, $endDate);
        
        return $this->json($trips);
    }
    
    /**
     * Get single trip
     */
    public function get(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $tripModel = new Trip($this->app);
        $trip = $tripModel->find($id);
        
        if (!$trip) {
            return $this->json(['error' => 'Trip not found'], 404);
        }
        
        return $this->json($trip);
    }
    
    /**
     * Get trip playback data (telemetry points)
     */
    public function getPlayback(array $params): array
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $tripModel = new Trip($this->app);
        $trip = $tripModel->find($id);
        
        if (!$trip) {
            return $this->json(['error' => 'Trip not found'], 404);
        }
        
        $telemetryModel = new Telemetry($this->app);
        $telemetry = $telemetryModel->getByDeviceAndRange(
            $trip['device_id'],
            $trip['start_time'],
            $trip['end_time']
        );
        
        return $this->json([
            'trip' => $trip,
            'telemetry' => $telemetry,
        ]);
    }
    
    /**
     * Export trip
     */
    public function export(array $params): void
    {
        $this->requireTenant();
        $this->requireRole('ReadOnly');
        
        $id = (int)($params['id'] ?? 0);
        $format = $this->input('format', 'csv');
        
        $tripModel = new Trip($this->app);
        $trip = $tripModel->find($id);
        
        if (!$trip) {
            http_response_code(404);
            echo json_encode(['error' => 'Trip not found']);
            exit;
        }
        
        if ($format === 'csv') {
            $telemetryModel = new Telemetry($this->app);
            $telemetry = $telemetryModel->getByDeviceAndRange(
                $trip['device_id'],
                $trip['start_time'],
                $trip['end_time']
            );
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="trip_' . $id . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Timestamp', 'Latitude', 'Longitude', 'Speed', 'Heading']);
            
            foreach ($telemetry as $point) {
                fputcsv($output, [
                    $point['timestamp'],
                    $point['lat'],
                    $point['lon'],
                    $point['speed'] ?? '',
                    $point['heading'] ?? '',
                ]);
            }
            
            fclose($output);
            exit;
        }
        
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported format']);
        exit;
    }
}

