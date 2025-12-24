<?php

/**
 * Dashboard Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/devices.php';
require_once __DIR__ . '/../includes/assets.php';
require_once __DIR__ . '/../includes/alerts.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Dashboard - DragNet Portal';
$tenantId = require_tenant();

// Get dashboard data
$totalDevices = device_count_by_status('online', $tenantId) + device_count_by_status('offline', $tenantId);
$onlineDevices = device_count_by_status('online', $tenantId);
$offlineDevices = device_count_by_status('offline', $tenantId);
$totalAssets = asset_count($tenantId);
$activeAlerts = alert_count(['acknowledged' => false], $tenantId);
$criticalAlerts = alert_count(['acknowledged' => false, 'severity' => 'critical'], $tenantId);

// Device status breakdown
$devices = device_list_all($tenantId);
$moving = 0;
$idle = 0;
$parked = 0;

foreach ($devices as $device) {
    device_update_status($device['id'], $tenantId);
    $device = device_find($device['id'], $tenantId);
    
    if ($device['status'] === 'moving') {
        $moving++;
    } elseif ($device['status'] === 'idle') {
        $idle++;
    } elseif ($device['status'] === 'parked') {
        $parked++;
    }
}

// Get recent alerts
$recentAlerts = alert_list_all(['acknowledged' => false], $tenantId);
$recentAlerts = array_slice($recentAlerts, 0, 5);

ob_start();
?>

<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-home me-2"></i>Dashboard</h1>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-microchip fa-2x text-primary mb-2"></i>
                <h3 class="mb-0"><?= $totalDevices ?></h3>
                <small class="text-muted">Total Devices</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-signal fa-2x text-success mb-2"></i>
                <h3 class="mb-0"><?= $onlineDevices ?></h3>
                <small class="text-muted">Online</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h3 class="mb-0"><?= $offlineDevices ?></h3>
                <small class="text-muted">Offline</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-car fa-2x text-info mb-2"></i>
                <h3 class="mb-0"><?= $totalAssets ?></h3>
                <small class="text-muted">Total Assets</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-bell fa-2x text-warning mb-2"></i>
                <h3 class="mb-0"><?= $activeAlerts ?></h3>
                <small class="text-muted">Active Alerts</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                <h3 class="mb-0"><?= $criticalAlerts ?></h3>
                <small class="text-muted">Critical</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Device Status</h5>
            </div>
            <div class="card-body">
                <canvas id="deviceStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentAlerts)): ?>
                    <p class="text-muted">No recent alerts</p>
                <?php else: ?>
                    <?php foreach ($recentAlerts as $alert): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong><?= h($alert['type']) ?></strong>
                            <br><small class="text-muted"><?= h($alert['message'] ?? '') ?></small>
                        </div>
                        <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : 'warning' ?>">
                            <?= h($alert['severity']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ctx = document.getElementById('deviceStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Moving', 'Idle', 'Parked'],
            datasets: [{
                data: [<?= $moving ?>, <?= $idle ?>, <?= $parked ?>],
                backgroundColor: ['#28a745', '#ffc107', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

