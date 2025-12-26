<?php

/**
 * Report Generation Functions
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Generate a report
 */
function generate_report(string $type, string $startDate, string $endDate, string $format = 'html', array $filters = []): string
{
    $tenantId = require_tenant();
    
    switch ($type) {
        case 'distance':
            return generate_distance_report($tenantId, $startDate, $endDate, $format, $filters);
        case 'idle':
            return generate_idle_report($tenantId, $startDate, $endDate, $format, $filters);
        case 'violations':
            return generate_violations_report($tenantId, $startDate, $endDate, $format, $filters);
        case 'fuel':
            return generate_fuel_report($tenantId, $startDate, $endDate, $format, $filters);
        case 'activity':
            return generate_activity_report($tenantId, $startDate, $endDate, $format, $filters);
        case 'health':
            return generate_health_report($tenantId, $startDate, $endDate, $format, $filters);
        default:
            throw new Exception('Unknown report type');
    }
}

/**
 * Generate Distance Report
 */
function generate_distance_report(int $tenantId, string $startDate, string $endDate, string $format, array $filters = []): string
{
    $where = ["d.tenant_id = :tenant_id", "DATE(t.timestamp) BETWEEN :start_date AND :end_date", "t.odometer IS NOT NULL"];
    $params = [
        'tenant_id' => $tenantId,
        'start_date' => $startDate,
        'end_date' => $endDate
    ];
    
    if (!empty($filters['asset_id'])) {
        $where[] = "d.asset_id = :asset_id";
        $params['asset_id'] = $filters['asset_id'];
    }
    
    if (!empty($filters['device_id'])) {
        $where[] = "d.id = :device_id";
        $params['device_id'] = $filters['device_id'];
    }
    
    $data = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            a.name as asset_name,
            MIN(t.timestamp) as first_seen,
            MAX(t.timestamp) as last_seen,
            MIN(t.odometer) as start_odometer,
            MAX(t.odometer) as end_odometer,
            COALESCE(MAX(t.odometer) - MIN(t.odometer), 0) as distance_km,
            COUNT(*) as data_points
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY d.id, d.device_uid, d.imei, a.name
        ORDER BY distance_km DESC",
        $params
    );
    
    $totalDistance = array_sum(array_column($data, 'distance_km'));
    
    return render_report_html('Distance Report', 'Total distance traveled by assets', $data, [
        'columns' => ['Device UID', 'IMEI', 'Asset', 'Start Odometer', 'End Odometer', 'Distance (km)', 'Data Points'],
        'fields' => ['device_uid', 'imei', 'asset_name', 'start_odometer', 'end_odometer', 'distance_km', 'data_points'],
        'summary' => ['Total Distance' => number_format($totalDistance, 2) . ' km']
    ], $startDate, $endDate, $format);
}

/**
 * Generate Idle Time Report
 */
function generate_idle_report(int $tenantId, string $startDate, string $endDate, string $format): string
{
    // Calculate idle time (ignition on but speed < 5 km/h for > 5 minutes)
    $data = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            a.name as asset_name,
            COUNT(CASE WHEN t.ignition = 1 AND (t.speed IS NULL OR t.speed < 5) THEN 1 END) as idle_events,
            SUM(CASE WHEN t.ignition = 1 AND (t.speed IS NULL OR t.speed < 5) THEN 1 ELSE 0 END) * 30 / 60.0 as idle_hours,
            MAX(t.timestamp) as last_idle
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE d.tenant_id = :tenant_id
        AND DATE(t.timestamp) BETWEEN :start_date AND :end_date
        GROUP BY d.id, d.device_uid, d.imei, a.name
        HAVING idle_events > 0
        ORDER BY idle_hours DESC",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    $totalIdleHours = array_sum(array_column($data, 'idle_hours'));
    
    return render_report_html('Idle Time Report', 'Analysis of idle time for assets', $data, [
        'columns' => ['Device UID', 'IMEI', 'Asset', 'Idle Events', 'Idle Hours', 'Last Idle'],
        'fields' => ['device_uid', 'imei', 'asset_name', 'idle_events', 'idle_hours', 'last_idle'],
        'summary' => ['Total Idle Hours' => number_format($totalIdleHours, 2) . ' hours']
    ], $startDate, $endDate, $format);
}

/**
 * Generate Violations Report
 */
function generate_violations_report(int $tenantId, string $startDate, string $endDate, string $format): string
{
    // Speed violations (> 100 km/h) and geofence events
    $speedViolations = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            a.name as asset_name,
            t.timestamp,
            t.speed,
            t.lat,
            t.lon
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE d.tenant_id = :tenant_id
        AND DATE(t.timestamp) BETWEEN :start_date AND :end_date
        AND t.speed > 100
        ORDER BY t.timestamp DESC",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    $geofenceEvents = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            a.name as asset_name,
            al.type,
            al.severity,
            al.message,
            al.created_at as timestamp
        FROM alerts al
        INNER JOIN devices d ON al.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE al.tenant_id = :tenant_id
        AND DATE(al.created_at) BETWEEN :start_date AND :end_date
        AND al.type IN ('geofence_entry', 'geofence_exit', 'speed_violation')
        ORDER BY al.created_at DESC",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    // Combine and format violations data
    $combinedData = [];
    foreach ($speedViolations as $v) {
        $combinedData[] = [
            'device_uid' => $v['device_uid'],
            'imei' => $v['imei'],
            'asset_name' => $v['asset_name'] ?? 'N/A',
            'type' => 'Speed Violation',
            'details' => 'Speed: ' . number_format($v['speed'], 2) . ' km/h',
            'timestamp' => $v['timestamp']
        ];
    }
    foreach ($geofenceEvents as $e) {
        $combinedData[] = [
            'device_uid' => $e['device_uid'],
            'imei' => $e['imei'],
            'asset_name' => $e['asset_name'] ?? 'N/A',
            'type' => ucfirst(str_replace('_', ' ', $e['type'])),
            'details' => $e['message'] ?? '',
            'timestamp' => $e['timestamp']
        ];
    }
    
    return render_report_html('Violations Report', 'Speed violations and geofence events', $combinedData, [
        'columns' => ['Device UID', 'IMEI', 'Asset', 'Type', 'Details', 'Timestamp'],
        'fields' => ['device_uid', 'imei', 'asset_name', 'type', 'details', 'timestamp'],
        'summary' => [
            'Speed Violations' => count($speedViolations),
            'Geofence Events' => count($geofenceEvents)
        ]
    ], $startDate, $endDate, $format);
}

/**
 * Generate Fuel Consumption Report
 */
function generate_fuel_report(int $tenantId, string $startDate, string $endDate, string $format): string
{
    $data = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            a.name as asset_name,
            AVG(t.fuel_level) as avg_fuel_level,
            MIN(t.fuel_level) as min_fuel_level,
            MAX(t.fuel_level) as max_fuel_level,
            COUNT(CASE WHEN t.fuel_level < 20 THEN 1 END) as low_fuel_events,
            MAX(t.odometer) - MIN(t.odometer) as distance_km
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE d.tenant_id = :tenant_id
        AND DATE(t.timestamp) BETWEEN :start_date AND :end_date
        AND t.fuel_level IS NOT NULL
        GROUP BY d.id, d.device_uid, d.imei, a.name
        ORDER BY distance_km DESC",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    return render_report_html('Fuel Consumption Report', 'Fuel usage analysis based on telemetry data', $data, [
        'columns' => ['Device UID', 'IMEI', 'Asset', 'Avg Fuel %', 'Min Fuel %', 'Max Fuel %', 'Low Fuel Events', 'Distance (km)'],
        'fields' => ['device_uid', 'imei', 'asset_name', 'avg_fuel_level', 'min_fuel_level', 'max_fuel_level', 'low_fuel_events', 'distance_km'],
        'summary' => []
    ], $startDate, $endDate, $format);
}

/**
 * Generate Activity Summary Report
 */
function generate_activity_report(int $tenantId, string $startDate, string $endDate, string $format): string
{
    $data = db_fetch_all(
        "SELECT 
            DATE(t.timestamp) as date,
            d.device_uid,
            d.imei,
            a.name as asset_name,
            COUNT(*) as data_points,
            SUM(CASE WHEN t.ignition = 1 THEN 1 ELSE 0 END) * 30 / 60.0 as operating_hours,
            MAX(t.speed) as max_speed,
            AVG(t.speed) as avg_speed
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE d.tenant_id = :tenant_id
        AND DATE(t.timestamp) BETWEEN :start_date AND :end_date
        GROUP BY DATE(t.timestamp), d.id, d.device_uid, d.imei, a.name
        ORDER BY date DESC, d.device_uid",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    return render_report_html('Activity Summary Report', 'Daily activity summary with hours of operation', $data, [
        'columns' => ['Date', 'Device UID', 'IMEI', 'Asset', 'Data Points', 'Operating Hours', 'Max Speed', 'Avg Speed'],
        'fields' => ['date', 'device_uid', 'imei', 'asset_name', 'data_points', 'operating_hours', 'max_speed', 'avg_speed'],
        'summary' => []
    ], $startDate, $endDate, $format);
}

/**
 * Generate Device Health Report
 */
function generate_health_report(int $tenantId, string $startDate, string $endDate, string $format): string
{
    $data = db_fetch_all(
        "SELECT 
            d.device_uid,
            d.imei,
            d.status,
            a.name as asset_name,
            AVG(t.gsm_signal) as avg_gsm_signal,
            AVG(t.battery_voltage) as avg_battery_voltage,
            AVG(t.internal_battery_level) as avg_internal_battery,
            MIN(t.timestamp) as first_seen,
            MAX(t.timestamp) as last_seen,
            COUNT(*) as data_points,
            COUNT(CASE WHEN t.gsm_signal < 30 THEN 1 END) as low_signal_events,
            COUNT(CASE WHEN t.battery_voltage < 12.0 THEN 1 END) as low_voltage_events
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON d.asset_id = a.id
        WHERE d.tenant_id = :tenant_id
        AND DATE(t.timestamp) BETWEEN :start_date AND :end_date
        GROUP BY d.id, d.device_uid, d.imei, d.status, a.name
        ORDER BY d.device_uid",
        [
            'tenant_id' => $tenantId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    );
    
    return render_report_html('Device Health Report', 'Device status, battery levels, and connectivity reports', $data, [
        'columns' => ['Device UID', 'IMEI', 'Status', 'Asset', 'Avg GSM Signal', 'Avg Battery V', 'Avg Internal %', 'Low Signal Events', 'Low Voltage Events', 'Data Points'],
        'fields' => ['device_uid', 'imei', 'status', 'asset_name', 'avg_gsm_signal', 'avg_battery_voltage', 'avg_internal_battery', 'low_signal_events', 'low_voltage_events', 'data_points'],
        'summary' => []
    ], $startDate, $endDate, $format);
}

/**
 * Render report as HTML
 */
function render_report_html(string $title, string $description, array $data, array $config, string $startDate, string $endDate, string $format): string
{
    $tenantId = require_tenant();
    $tenant = db_fetch_one("SELECT name FROM tenants WHERE id = :id", ['id' => $tenantId]);
    $tenantName = $tenant['name'] ?? 'Unknown';
    
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - Dragnet Intelematics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .report-container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .report-header { border-bottom: 3px solid #1a1a1a; padding-bottom: 20px; margin-bottom: 30px; }
        .report-header h1 { color: #1a1a1a; font-size: 28px; margin-bottom: 10px; }
        .report-header .subtitle { color: #666; font-size: 14px; }
        .report-meta { display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; }
        .report-meta div { margin: 5px 0; }
        .report-meta strong { color: #1a1a1a; }
        .report-summary { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .report-summary h3 { margin-bottom: 10px; color: #1a1a1a; }
        .report-summary ul { list-style: none; }
        .report-summary li { padding: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1a1a1a; color: white; padding: 12px; text-align: left; font-weight: bold; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #f0f0f0; }
        .no-data { text-align: center; padding: 40px; color: #999; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 12px; }
        @media print {
            body { background: white; padding: 0; }
            .report-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1>' . htmlspecialchars($title) . '</h1>
            <div class="subtitle">' . htmlspecialchars($description) . '</div>
        </div>
        
        <div class="report-meta">
            <div><strong>Tenant:</strong> ' . htmlspecialchars($tenantName) . '</div>
            <div><strong>Period:</strong> ' . htmlspecialchars($startDate) . ' to ' . htmlspecialchars($endDate) . '</div>
            <div><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</div>
        </div>';
    
    if (!empty($config['summary'])) {
        $html .= '<div class="report-summary">
            <h3>Summary</h3>
            <ul>';
        foreach ($config['summary'] as $label => $value) {
            $html .= '<li><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars($value) . '</li>';
        }
        $html .= '</ul></div>';
    }
    
    $html .= '<table>
            <thead>
                <tr>';
    foreach ($config['columns'] as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr>
            </thead>
            <tbody>';
    
    if (empty($data)) {
        $html .= '<tr><td colspan="' . count($config['columns']) . '" class="no-data">No data available for the selected period</td></tr>';
    } else {
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($config['fields'] as $field) {
                $value = $row[$field] ?? 'N/A';
                if (is_numeric($value) && strpos($field, 'date') === false && strpos($field, 'timestamp') === false) {
                    $value = number_format((float)$value, 2);
                } elseif (strpos($field, 'date') !== false || strpos($field, 'timestamp') !== false) {
                    $value = $value ? date('Y-m-d H:i', strtotime($value)) : 'N/A';
                }
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody>
        </table>
        
        <div class="footer">
            <p>Generated by Dragnet Intelematics - ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

