<?php

/**
 * Devices List Page
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

$title = 'Devices - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$devices = device_list_all($tenantId);

// Update status for all devices
foreach ($devices as &$device) {
    device_update_status($device['id'], $tenantId);
    $device = device_find($device['id'], $tenantId);
}

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-microchip me-2"></i>Devices</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Device UID</th>
                    <th>IMEI</th>
                    <th>Model</th>
                    <th>Status</th>
                    <th>Signal</th>
                    <th>Voltage</th>
                    <th>Battery</th>
                    <th>Last Seen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($devices)): ?>
                <tr>
                    <td colspan="9" class="text-center">No devices found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($devices as $device): ?>
                <tr>
                    <td><?= h($device['device_uid']) ?></td>
                    <td><?= h($device['imei']) ?></td>
                    <td><?= h($device['model'] ?? 'FMM13A') ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $device['status'] === 'online' || $device['status'] === 'moving' ? 'success' : 
                            ($device['status'] === 'idle' ? 'warning' : 'danger') 
                        ?>">
                            <?= h($device['status']) ?>
                        </span>
                    </td>
                    <td><?= $device['gsm_signal'] !== null ? $device['gsm_signal'] : '-' ?></td>
                    <td><?= $device['external_voltage'] !== null ? number_format($device['external_voltage'], 2) . 'V' : '-' ?></td>
                    <td><?= $device['internal_battery'] !== null ? $device['internal_battery'] . '%' : '-' ?></td>
                    <td><?= format_datetime($device['last_seen']) ?></td>
                    <td>
                        <a href="/devices/detail.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

