<?php

/**
 * Controller Functions (Procedural)
 * All request handlers
 */

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/**
 * Auth Controllers
 */
function auth_login(): string
{
    global $config;
    $ssoConfig = $config['sso'];
    
    return view('auth/login', [
        'entraEnabled' => $ssoConfig['providers']['entra']['enabled'],
        'googleEnabled' => $ssoConfig['providers']['google']['enabled'],
    ]);
}

function auth_callback(): void
{
    $email = input('email');
    $tenantId = (int)input('tenant_id', 1);
    $provider = input('provider', 'oauth');
    
    // For development/test logins (when SSO not configured)
    if ($provider === 'dev' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $email)) {
        if (!$email) {
            redirect('/login?error=email_required');
        }
        
        // Verify tenant exists
        $tenant = db_fetch_one("SELECT id FROM tenants WHERE id = :id", ['id' => $tenantId]);
        
        if (!$tenant) {
            redirect('/login?error=tenant_not_found');
        }
        
        // For dev mode, set default role to Administrator for first user
        $existingUser = user_find_by_email($email, $tenantId);
        $role = $existingUser ? $existingUser['role'] : 'Administrator';
        
        $user = user_find_or_create_from_sso($email, $tenantId, 'dev', 'dev_' . $email, $role);
        
        // Create tenant context
        set_tenant_context([
            'tenant_id' => $tenantId,
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_role' => $user['role'],
        ]);
        
        redirect('/dashboard');
    }
    
    redirect('/login?error=invalid_callback');
}

function auth_logout(): void
{
    session_destroy_custom();
    redirect('/login');
}

function auth_saml(): void
{
    redirect('/login?error=saml_not_configured');
}

function auth_oauth(): void
{
    redirect('/login?error=oauth_not_configured');
}

/**
 * Dashboard Controllers
 */
function dashboard_index(): string
{
    return view('dashboard/index');
}

function dashboard_get_widgets(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    
    // Total devices
    $totalDevices = model_count('devices', [], $tenantId);
    
    // Online vs offline
    $onlineDevices = model_count('devices', ['status' => 'online'], $tenantId);
    $offlineDevices = model_count('devices', ['status' => 'offline'], $tenantId);
    
    // Total assets
    $totalAssets = model_count('assets', [], $tenantId);
    
    // Active alerts
    $activeAlerts = model_count('alerts', ['acknowledged' => false], $tenantId);
    $criticalAlerts = model_count('alerts', ['acknowledged' => false, 'severity' => 'critical'], $tenantId);
    
    // Device status breakdown
    $devices = model_find_all('devices', [], 'id DESC', $tenantId);
    $moving = 0;
    $idle = 0;
    $parked = 0;
    
    foreach ($devices as $device) {
        $telemetry = device_get_latest_telemetry($device['id']);
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
    
    json_response([
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

/**
 * Map Controllers
 */
function map_index(): string
{
    return view('map/index');
}

function map_get_devices(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $devices = device_find_all_with_status($tenantId);
    
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
    
    json_response($result);
}

function map_get_geofences(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $geofences = geofence_find_all_active($tenantId);
    
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
    
    json_response($result);
}

/**
 * Asset Controllers
 */
function asset_index(): string
{
    return view('assets/index');
}

function asset_list(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $assets = asset_find_all_with_devices([], $tenantId);
    
    json_response($assets);
}

function asset_show(array $params): string
{
    $id = (int)($params['id'] ?? 0);
    return view('assets/detail', ['asset_id' => $id]);
}

function asset_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    $asset = asset_find_with_device($id, $tenantId);
    
    if (!$asset) {
        json_response(['error' => 'Asset not found'], 404);
    }
    
    json_response($asset);
}

function asset_create(): void
{
    require_tenant();
    require_role('Operator');
    
    $tenantId = require_tenant();
    $data = [
        'name' => input('name'),
        'vehicle_id' => input('vehicle_id'),
        'device_id' => input('device_id') ? (int)input('device_id') : null,
        'status' => input('status', 'active'),
    ];
    
    if (empty($data['name'])) {
        json_response(['error' => 'Name is required'], 400);
    }
    
    $id = model_create('assets', $data, $tenantId);
    json_response(['id' => $id, 'message' => 'Asset created']);
}

function asset_update(array $params): void
{
    require_tenant();
    require_role('Operator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('assets', $id, $tenantId)) {
        json_response(['error' => 'Asset not found'], 404);
    }
    
    $data = [];
    if (input('name') !== null) {
        $data['name'] = input('name');
    }
    if (input('vehicle_id') !== null) {
        $data['vehicle_id'] = input('vehicle_id');
    }
    if (input('device_id') !== null) {
        $data['device_id'] = input('device_id') ? (int)input('device_id') : null;
    }
    if (input('status') !== null) {
        $data['status'] = input('status');
    }
    
    if (empty($data)) {
        json_response(['error' => 'No data to update'], 400);
    }
    
    model_update('assets', $id, $data, $tenantId);
    json_response(['message' => 'Asset updated']);
}

function asset_delete(array $params): void
{
    require_tenant();
    require_role('Administrator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('assets', $id, $tenantId)) {
        json_response(['error' => 'Asset not found'], 404);
    }
    
    model_delete('assets', $id, $tenantId);
    json_response(['message' => 'Asset deleted']);
}

/**
 * Device Controllers
 */
function device_index(): string
{
    return view('devices/index');
}

function device_list(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $devices = model_find_all('devices', [], 'id DESC', $tenantId);
    
    json_response($devices);
}

function device_show(array $params): string
{
    $id = (int)($params['id'] ?? 0);
    return view('devices/detail', ['device_id' => $id]);
}

function device_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    $device = model_find('devices', $id, $tenantId);
    
    if (!$device) {
        json_response(['error' => 'Device not found'], 404);
    }
    
    $device['latest_telemetry'] = device_get_latest_telemetry($id);
    json_response($device);
}

function device_get_telemetry(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $startTime = input('start_time', date('Y-m-d H:i:s', strtotime('-24 hours')));
    $endTime = input('end_time', date('Y-m-d H:i:s'));
    
    $telemetry = telemetry_get_by_device_and_range($id, $startTime, $endTime);
    json_response($telemetry);
}

function device_get_status(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    $device = model_find('devices', $id, $tenantId);
    
    if (!$device) {
        json_response(['error' => 'Device not found'], 404);
    }
    
    device_update_status($id);
    $device = model_find('devices', $id, $tenantId);
    
    json_response([
        'status' => $device['status'],
        'last_checkin' => $device['last_checkin'],
        'battery_level' => $device['battery_level'],
        'signal_strength' => $device['signal_strength'],
    ]);
}

function device_register_teltonika(): void
{
    require_tenant();
    require_role('Operator');
    
    $imei = input('imei');
    $deviceUid = input('device_uid');
    $tenantId = require_tenant();
    
    if (empty($imei)) {
        json_response(['error' => 'IMEI is required'], 400);
    }
    
    $device = device_register_teltonika($imei, $tenantId, $deviceUid);
    json_response([
        'id' => $device['id'],
        'message' => 'Teltonika device registered',
        'device' => $device
    ]);
}

/**
 * Alert Controllers
 */
function alert_index(): string
{
    return view('alerts/index');
}

function alert_list(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $filters = [];
    
    if (input('type')) {
        $filters['type'] = input('type');
    }
    if (input('severity')) {
        $filters['severity'] = input('severity');
    }
    
    $acknowledged = input('acknowledged');
    if ($acknowledged !== null && $acknowledged === 'false') {
        $alerts = alert_get_unacknowledged($filters, $tenantId);
    } else {
        $alerts = model_find_all('alerts', $filters, 'created_at DESC', $tenantId);
    }
    
    json_response($alerts);
}

function alert_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    $alert = model_find('alerts', $id, $tenantId);
    
    if (!$alert) {
        json_response(['error' => 'Alert not found'], 404);
    }
    
    json_response($alert);
}

function alert_acknowledge(array $params): void
{
    require_tenant();
    require_role('Operator');
    
    $id = (int)($params['id'] ?? 0);
    $context = get_tenant_context();
    $tenantId = $context['tenant_id'];
    
    $alert = model_find('alerts', $id, $tenantId);
    if (!$alert) {
        json_response(['error' => 'Alert not found'], 404);
    }
    
    // Use model_update instead of recursive call
    model_update('alerts', $id, [
        'acknowledged' => true,
        'acknowledged_by' => $context['user_id'],
        'acknowledged_at' => date('Y-m-d H:i:s')
    ], $tenantId);
    
    json_response(['message' => 'Alert acknowledged']);
}

function alert_assign(array $params): void
{
    require_tenant();
    require_role('Operator');
    
    $id = (int)($params['id'] ?? 0);
    $userId = (int)input('user_id');
    $tenantId = require_tenant();
    
    $alert = model_find('alerts', $id, $tenantId);
    if (!$alert) {
        json_response(['error' => 'Alert not found'], 404);
    }
    
    model_update('alerts', $id, ['assigned_to' => $userId], $tenantId);
    json_response(['message' => 'Alert assigned']);
}

function alert_export(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $filters = [];
    
    if (input('type')) {
        $filters['type'] = input('type');
    }
    if (input('severity')) {
        $filters['severity'] = input('severity');
    }
    
    $alerts = model_find_all('alerts', $filters, 'created_at DESC', $tenantId);
    
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

