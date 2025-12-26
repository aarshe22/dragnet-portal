<?php

/**
 * Geofence Analytics Page
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/tenant.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/geofences.php';
require_once __DIR__ . '/../../includes/devices.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Geofence Analytics - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$geofenceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$geofence = geofence_find($geofenceId, $tenantId);

if (!$geofence) {
    http_response_code(404);
    echo 'Geofence not found';
    exit;
}

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get analytics
$analytics = geofence_get_analytics($geofenceId, $tenantId, $startDate, $endDate);

// Get devices currently inside
$devicesInside = geofence_get_devices_inside($geofenceId, $tenantId);

// Get recent events
$recentEvents = geofence_get_events($geofenceId, $tenantId, null, $startDate, $endDate, 50);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-chart-line me-2"></i>Geofence Analytics: <?= h($geofence['name']) ?></h1>
    </div>
    <div class="col-auto">
        <a href="/geofences.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Geofences
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="id" value="<?= $geofenceId ?>">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= h($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= h($endDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($analytics['visits']) ?></h3>
                <small class="text-muted">Devices Visited</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $analytics['total_entries'] ?></h3>
                <small class="text-muted">Total Entries</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= $analytics['total_exits'] ?></h3>
                <small class="text-muted">Total Exits</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($devicesInside) ?></h3>
                <small class="text-muted">Currently Inside</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Visit Statistics</h5>
            </div>
            <div class="card-body">
                <?php if (empty($analytics['visits'])): ?>
                <p class="text-muted text-center py-3">No visits recorded</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Asset</th>
                                <th>Entries</th>
                                <th>Exits</th>
                                <th>First Visit</th>
                                <th>Last Visit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics['visits'] as $visit): ?>
                            <tr>
                                <td><?= h($visit['device_uid']) ?></td>
                                <td><?= h($visit['asset_name'] ?? '-') ?></td>
                                <td><span class="badge bg-success"><?= $visit['entry_count'] ?></span></td>
                                <td><span class="badge bg-warning"><?= $visit['exit_count'] ?></span></td>
                                <td><?= $visit['first_visit'] ? format_datetime($visit['first_visit']) : '-' ?></td>
                                <td><?= $visit['last_visit'] ? format_datetime($visit['last_visit']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Devices Currently Inside</h5>
            </div>
            <div class="card-body">
                <?php if (empty($devicesInside)): ?>
                <p class="text-muted text-center py-3">No devices currently inside</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Asset</th>
                                <th>Entry Time</th>
                                <th>Dwell Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devicesInside as $device): ?>
                            <tr>
                                <td><?= h($device['device_uid']) ?></td>
                                <td><?= h($device['asset_name'] ?? '-') ?></td>
                                <td><?= format_datetime($device['entry_time']) ?></td>
                                <td><?= $device['dwell_minutes'] ?> minutes</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Events</h5>
    </div>
    <div class="card-body">
        <?php if (empty($recentEvents)): ?>
        <p class="text-muted text-center py-3">No events recorded</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Device</th>
                        <th>Asset</th>
                        <th>Event</th>
                        <th>Speed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEvents as $event): ?>
                    <tr>
                        <td><?= format_datetime($event['timestamp']) ?></td>
                        <td><?= h($event['device_uid']) ?></td>
                        <td><?= h($event['asset_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $event['event_type'] === 'entry' ? 'success' : 'warning' ?>">
                                <?= ucfirst($event['event_type']) ?>
                            </span>
                        </td>
                        <td><?= $event['speed'] ? number_format($event['speed'], 1) . ' km/h' : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
?>

