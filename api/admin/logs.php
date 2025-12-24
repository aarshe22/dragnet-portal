<?php

/**
 * API: Telematics Logs
 */

// Load configuration first
$config = require __DIR__ . '/../../config.php';
$GLOBALS['config'] = $config;

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('Administrator');

$tenantId = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : null;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'timestamp_desc';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;

$where = [];
$params = [];

if ($tenantId) {
    $where[] = "d.tenant_id = :tenant_id";
    $params['tenant_id'] = $tenantId;
}

if ($deviceId) {
    $where[] = "t.device_id = :device_id";
    $params['device_id'] = $deviceId;
}

if ($search) {
    $where[] = "(d.device_uid LIKE :search OR d.imei LIKE :search OR t.lat LIKE :search OR t.lon LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sort order
$orderBy = 't.timestamp DESC';
switch ($sort) {
    case 'timestamp_asc':
        $orderBy = 't.timestamp ASC';
        break;
    case 'timestamp_desc':
        $orderBy = 't.timestamp DESC';
        break;
    case 'device_asc':
        $orderBy = 'd.device_uid ASC';
        break;
    case 'device_desc':
        $orderBy = 'd.device_uid DESC';
        break;
}

$sql = "SELECT t.*, d.device_uid, d.imei, d.tenant_id, tn.name as tenant_name
        FROM telemetry t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN tenants tn ON d.tenant_id = tn.id
        {$whereClause}
        ORDER BY {$orderBy}
        LIMIT :limit";

$params['limit'] = $limit;

$logs = db_fetch_all($sql, $params);

json_response($logs);

