<?php

/**
 * Device Detail Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/devices.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Device Detail - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$deviceId = (int)($_GET['id'] ?? 0);
$device = device_find($deviceId, $tenantId);

if (!$device) {
    http_response_code(404);
    echo 'Device not found';
    exit;
}

device_update_status($deviceId, $tenantId);
$device = device_find($deviceId, $tenantId);

$telemetry = device_get_latest_telemetry($deviceId);
$ioPayload = $telemetry ? json_decode($telemetry['io_payload'] ?? '{}', true) : [];

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-microchip me-2"></i>Device: <?= h($device['device_uid']) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Device Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Device UID:</th>
                        <td><?= h($device['device_uid']) ?></td>
                    </tr>
                    <tr>
                        <th>IMEI:</th>
                        <td><?= h($device['imei']) ?></td>
                    </tr>
                    <tr>
                        <th>ICCID:</th>
                        <td><?= h($device['iccid'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Model:</th>
                        <td><?= h($device['model'] ?? 'FMM13A') ?></td>
                    </tr>
                    <tr>
                        <th>Firmware:</th>
                        <td><?= h($device['firmware_version'] ?? '-') ?></td>
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
                    <tr>
                        <th>Last Seen:</th>
                        <td><?= format_datetime($device['last_seen']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Device Health</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>GSM Signal:</th>
                        <td><?= $device['gsm_signal'] !== null ? $device['gsm_signal'] : '-' ?></td>
                    </tr>
                    <tr>
                        <th>External Voltage:</th>
                        <td><?= $device['external_voltage'] !== null ? number_format($device['external_voltage'], 2) . 'V' : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Internal Battery:</th>
                        <td><?= $device['internal_battery'] !== null ? $device['internal_battery'] . '%' : '-' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <?php if ($telemetry): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Latest Position</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Timestamp:</th>
                        <td><?= format_datetime($telemetry['timestamp']) ?></td>
                    </tr>
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
                        <th>Heading:</th>
                        <td><?= $telemetry['heading'] !== null ? $telemetry['heading'] . '°' : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Satellites:</th>
                        <td><?= $telemetry['satellites'] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <th>Ignition:</th>
                        <td>
                            <span class="badge bg-<?= $telemetry['ignition'] ? 'success' : 'secondary' ?>">
                                <?= $telemetry['ignition'] ? 'On' : 'Off' ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if (!empty($ioPayload)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">IO Elements</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <?php foreach ($ioPayload as $key => $value): ?>
                    <tr>
                        <th><?= h($key) ?>:</th>
                        <td><?= h($value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p class="text-muted">No telemetry data available</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

