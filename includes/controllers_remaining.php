<?php

/**
 * Additional Controller Functions
 */

/**
 * Geofence Controllers
 */
function geofence_index(): string
{
    return view('geofences/index');
}

function geofence_list(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $tenantId = require_tenant();
    $geofences = geofence_find_all_active($tenantId);
    json_response($geofences);
}

function geofence_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    $geofence = model_find('geofences', $id, $tenantId);
    
    if (!$geofence) {
        json_response(['error' => 'Geofence not found'], 404);
    }
    
    json_response($geofence);
}

function geofence_create(): void
{
    require_tenant();
    require_role('Operator');
    
    $tenantId = require_tenant();
    $data = [
        'name' => input('name'),
        'type' => input('type'),
        'coordinates' => json_encode(input('coordinates')),
        'rules' => json_encode(input('rules', [])),
        'active' => true,
    ];
    
    if (empty($data['name']) || empty($data['type']) || empty($data['coordinates'])) {
        json_response(['error' => 'Name, type, and coordinates are required'], 400);
    }
    
    $id = model_create('geofences', $data, $tenantId);
    json_response(['id' => $id, 'message' => 'Geofence created']);
}

function geofence_update(array $params): void
{
    require_tenant();
    require_role('Operator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('geofences', $id, $tenantId)) {
        json_response(['error' => 'Geofence not found'], 404);
    }
    
    $data = [];
    if (input('name') !== null) {
        $data['name'] = input('name');
    }
    if (input('coordinates') !== null) {
        $data['coordinates'] = json_encode(input('coordinates'));
    }
    if (input('rules') !== null) {
        $data['rules'] = json_encode(input('rules'));
    }
    if (input('active') !== null) {
        $data['active'] = input('active') === 'true' || input('active') === true;
    }
    
    if (empty($data)) {
        json_response(['error' => 'No data to update'], 400);
    }
    
    model_update('geofences', $id, $data, $tenantId);
    json_response(['message' => 'Geofence updated']);
}

function geofence_delete(array $params): void
{
    require_tenant();
    require_role('Administrator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('geofences', $id, $tenantId)) {
        json_response(['error' => 'Geofence not found'], 404);
    }
    
    model_delete('geofences', $id, $tenantId);
    json_response(['message' => 'Geofence deleted']);
}

/**
 * Trip Controllers
 */
function trip_index(array $params): string
{
    $assetId = (int)($params['id'] ?? 0);
    return view('trips/index', ['asset_id' => $assetId]);
}

function trip_list(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $assetId = (int)($params['id'] ?? 0);
    $startDate = input('start_date');
    $endDate = input('end_date');
    
    $trips = trip_get_by_asset($assetId, $startDate, $endDate);
    json_response($trips);
}

function trip_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $trip = db_fetch_one("SELECT * FROM trips WHERE id = :id", ['id' => $id]);
    
    if (!$trip) {
        json_response(['error' => 'Trip not found'], 404);
    }
    
    json_response($trip);
}

function trip_get_playback(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $trip = db_fetch_one("SELECT * FROM trips WHERE id = :id", ['id' => $id]);
    
    if (!$trip) {
        json_response(['error' => 'Trip not found'], 404);
    }
    
    $telemetry = telemetry_get_by_device_and_range(
        $trip['device_id'],
        $trip['start_time'],
        $trip['end_time']
    );
    
    json_response([
        'trip' => $trip,
        'telemetry' => $telemetry,
    ]);
}

function trip_export(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $format = input('format', 'csv');
    
    $trip = db_fetch_one("SELECT * FROM trips WHERE id = :id", ['id' => $id]);
    
    if (!$trip) {
        http_response_code(404);
        echo json_encode(['error' => 'Trip not found']);
        exit;
    }
    
    if ($format === 'csv') {
        $telemetry = telemetry_get_by_device_and_range(
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

/**
 * Video Controllers
 */
function video_index(array $params): string
{
    $assetId = (int)($params['id'] ?? 0);
    return view('video/index', ['asset_id' => $assetId]);
}

function video_list(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $assetId = (int)($params['id'] ?? 0);
    $startDate = input('start_date');
    $endDate = input('end_date');
    
    $asset = model_find('assets', $assetId);
    if (!$asset || !$asset['device_id']) {
        json_response([]);
    }
    
    $where = ["device_id = :device_id"];
    $params_query = ['device_id' => $asset['device_id']];
    
    if ($startDate) {
        $where[] = "start_time >= :start_date";
        $params_query['start_date'] = $startDate;
    }
    
    if ($endDate) {
        $where[] = "end_time <= :end_date";
        $params_query['end_date'] = $endDate;
    }
    
    $sql = "SELECT * FROM video_segments 
            WHERE " . implode(' AND ', $where) . "
            ORDER BY start_time DESC";
    
    $segments = db_fetch_all($sql, $params_query);
    json_response($segments);
}

function video_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    $sql = "SELECT vs.*, d.tenant_id
            FROM video_segments vs
            INNER JOIN devices d ON vs.device_id = d.id
            WHERE vs.id = :id AND d.tenant_id = :tenant_id";
    
    $segment = db_fetch_one($sql, ['id' => $id, 'tenant_id' => $tenantId]);
    
    if (!$segment) {
        json_response(['error' => 'Video segment not found'], 404);
    }
    
    json_response($segment);
}

function video_stream(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    $sql = "SELECT vs.*, d.tenant_id
            FROM video_segments vs
            INNER JOIN devices d ON vs.device_id = d.id
            WHERE vs.id = :id AND d.tenant_id = :tenant_id";
    
    $segment = db_fetch_one($sql, ['id' => $id, 'tenant_id' => $tenantId]);
    
    if (!$segment) {
        http_response_code(404);
        exit;
    }
    
    $filePath = $segment['file_path'];
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit;
    }
    
    header('Content-Type: video/mp4');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

function video_download(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    $sql = "SELECT vs.*, d.tenant_id
            FROM video_segments vs
            INNER JOIN devices d ON vs.device_id = d.id
            WHERE vs.id = :id AND d.tenant_id = :tenant_id";
    
    $segment = db_fetch_one($sql, ['id' => $id, 'tenant_id' => $tenantId]);
    
    if (!$segment) {
        http_response_code(404);
        exit;
    }
    
    $filePath = $segment['file_path'];
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit;
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="video_' . $id . '.mp4"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

/**
 * Report Controllers
 */
function report_index(): string
{
    return view('reports/index');
}

function report_list(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    json_response([
        ['id' => 'distance', 'name' => 'Distance Report', 'description' => 'Total distance traveled by assets'],
        ['id' => 'idle', 'name' => 'Idle Time Report', 'description' => 'Idle time analysis'],
        ['id' => 'violations', 'name' => 'Violations Report', 'description' => 'Speed and geofence violations'],
        ['id' => 'utilization', 'name' => 'Utilization Report', 'description' => 'Asset utilization metrics'],
        ['id' => 'connectivity', 'name' => 'Connectivity Report', 'description' => 'Device connectivity status'],
        ['id' => 'data_usage', 'name' => 'Data Usage Report', 'description' => 'Data and video usage statistics'],
    ]);
}

function report_get(array $params): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $id = $params['id'] ?? '';
    $startDate = input('start_date');
    $endDate = input('end_date');
    
    json_response([
        'report_id' => $id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'data' => [],
    ]);
}

function report_generate(): void
{
    require_tenant();
    require_role('ReadOnly');
    
    $reportType = input('type');
    $startDate = input('start_date');
    $endDate = input('end_date');
    
    json_response([
        'report_id' => uniqid(),
        'type' => $reportType,
        'status' => 'generated',
        'message' => 'Report generation initiated',
    ]);
}

/**
 * Admin Controllers
 */
function admin_index(): string
{
    require_tenant();
    require_role('Administrator');
    
    return view('admin/index');
}

function admin_users(): string
{
    require_tenant();
    require_role('Administrator');
    
    return view('admin/users');
}

function admin_list_users(): void
{
    require_tenant();
    require_role('Administrator');
    
    $tenantId = require_tenant();
    $users = model_find_all('users', [], 'email ASC', $tenantId);
    json_response($users);
}

function admin_create_user(): void
{
    require_tenant();
    require_role('Administrator');
    
    $tenantId = require_tenant();
    $data = [
        'email' => input('email'),
        'role' => input('role', 'Guest'),
    ];
    
    if (empty($data['email'])) {
        json_response(['error' => 'Email is required'], 400);
    }
    
    $existing = user_find_by_email($data['email'], $tenantId);
    if ($existing) {
        json_response(['error' => 'User already exists'], 400);
    }
    
    $id = model_create('users', $data, $tenantId);
    json_response(['id' => $id, 'message' => 'User created']);
}

function admin_update_user(array $params): void
{
    require_tenant();
    require_role('Administrator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('users', $id, $tenantId)) {
        json_response(['error' => 'User not found'], 404);
    }
    
    $data = [];
    if (input('email') !== null) {
        $data['email'] = input('email');
    }
    if (input('role') !== null) {
        $data['role'] = input('role');
    }
    
    if (empty($data)) {
        json_response(['error' => 'No data to update'], 400);
    }
    
    model_update('users', $id, $data, $tenantId);
    json_response(['message' => 'User updated']);
}

function admin_delete_user(array $params): void
{
    require_tenant();
    require_role('Administrator');
    
    $id = (int)($params['id'] ?? 0);
    $tenantId = require_tenant();
    
    if (!model_find('users', $id, $tenantId)) {
        json_response(['error' => 'User not found'], 404);
    }
    
    model_delete('users', $id, $tenantId);
    json_response(['message' => 'User deleted']);
}

function admin_get_settings(): void
{
    require_tenant();
    require_role('Administrator');
    
    json_response([
        'alert_config' => [],
        'system_defaults' => [],
    ]);
}

function admin_update_settings(): void
{
    require_tenant();
    require_role('Administrator');
    
    json_response(['message' => 'Settings updated']);
}

/**
 * Push Controllers
 */
function push_subscribe(): void
{
    require_tenant();
    
    $endpoint = input('endpoint');
    $p256dh = input('keys.p256dh');
    $auth = input('keys.auth');
    $platform = input('platform');
    
    if (!$endpoint || !$p256dh || !$auth) {
        json_response(['error' => 'Invalid subscription data'], 400);
    }
    
    $context = get_tenant_context();
    
    $existing = db_fetch_one(
        "SELECT id FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
        ['user_id' => $context['user_id'], 'endpoint' => $endpoint]
    );
    
    if ($existing) {
        db_execute(
            "UPDATE push_subscriptions SET p256dh_key = :p256dh, auth_key = :auth, platform = :platform, updated_at = NOW() WHERE id = :id",
            [
                'id' => $existing['id'],
                'p256dh' => $p256dh,
                'auth' => $auth,
                'platform' => $platform,
            ]
        );
        json_response(['message' => 'Subscription updated']);
    }
    
    db_execute(
        "INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, platform, user_agent) VALUES (:user_id, :endpoint, :p256dh, :auth, :platform, :user_agent)",
        [
            'user_id' => $context['user_id'],
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth,
            'platform' => $platform,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]
    );
    
    json_response(['message' => 'Subscribed to push notifications']);
}

function push_unsubscribe(): void
{
    require_tenant();
    
    $endpoint = input('endpoint');
    if (!$endpoint) {
        json_response(['error' => 'Endpoint required'], 400);
    }
    
    $context = get_tenant_context();
    
    db_execute(
        "DELETE FROM push_subscriptions WHERE user_id = :user_id AND endpoint = :endpoint",
        ['user_id' => $context['user_id'], 'endpoint' => $endpoint]
    );
    
    json_response(['message' => 'Unsubscribed from push notifications']);
}

/**
 * PWA Controllers
 */
function pwa_manifest(): void
{
    global $config;
    $pwaConfig = $config['pwa'];
    
    header('Content-Type: application/manifest+json');
    json_response([
        'name' => $pwaConfig['name'],
        'short_name' => $pwaConfig['short_name'],
        'description' => 'DragNet Telematics Portal',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => $pwaConfig['background_color'],
        'theme_color' => $pwaConfig['theme_color'],
        'orientation' => 'any',
        'icons' => [
            ['src' => '/public/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/public/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ],
    ]);
}

function pwa_service_worker(): void
{
    header('Content-Type: application/javascript');
    echo file_get_contents(__DIR__ . '/../public/service-worker.js');
    exit;
}

