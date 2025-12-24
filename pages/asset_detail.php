<?php

/**
 * Asset Detail Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/assets.php';
require_once __DIR__ . '/../includes/devices.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Asset Detail - DragNet Portal';
$showNav = true;
$tenantId = require_tenant();

$assetId = (int)($_GET['id'] ?? 0);
$asset = asset_find_with_device($assetId, $tenantId);

if (!$asset) {
    http_response_code(404);
    echo 'Asset not found';
    exit;
}

$device = null;
$telemetry = null;
if ($asset['device_id']) {
    $device = device_find($asset['device_id'], $tenantId);
    if ($device) {
        $telemetry = device_get_latest_telemetry($device['id']);
    }
}

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Asset: <?= h($asset['name']) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Asset Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Name:</th>
                        <td><?= h($asset['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Vehicle ID:</th>
                        <td><?= h($asset['vehicle_id'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= h($asset['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($device): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Assigned Device</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Device UID:</th>
                        <td><a href="/devices/detail.php?id=<?= $device['id'] ?>"><?= h($device['device_uid']) ?></a></td>
                    </tr>
                    <tr>
                        <th>IMEI:</th>
                        <td><?= h($device['imei']) ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?= 
                                $device['status'] === 'online' || $device['status'] === 'moving' ? 'success' : 
                                ($device['status'] === 'idle' ? 'warning' : 'danger') 
                            ?>">
                                <?= h($device['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <?php if ($telemetry): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Current Position</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Latitude:</th>
                        <td><?= number_format($telemetry['lat'], 6) ?></td>
                    </tr>
                    <tr>
                        <th>Longitude:</th>
                        <td><?= number_format($telemetry['lon'], 6) ?></td>
                    </tr>
                    <tr>
                        <th>Speed:</th>
                        <td><?= $telemetry['speed'] !== null ? number_format($telemetry['speed'], 1) . ' km/h' : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Last Update:</th>
                        <td><?= format_datetime($telemetry['timestamp']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p class="text-muted">No position data available</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

