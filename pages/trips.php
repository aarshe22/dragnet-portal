<?php

/**
 * Trips Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/trips.php';
require_once __DIR__ . '/../includes/devices.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Trips - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get trips
$where = ["t.tenant_id = :tenant_id"];
$params = ['tenant_id' => $tenantId];

if ($deviceId) {
    $where[] = "t.device_id = :device_id";
    $params['device_id'] = $deviceId;
}

if ($startDate) {
    $where[] = "DATE(t.start_time) >= :start_date";
    $params['start_date'] = $startDate;
}

if ($endDate) {
    $where[] = "DATE(t.start_time) <= :end_date";
    $params['end_date'] = $endDate;
}

$sql = "SELECT t.*, d.device_uid, a.name as asset_name
        FROM trips t
        INNER JOIN devices d ON t.device_id = d.id
        LEFT JOIN assets a ON t.asset_id = a.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY t.start_time DESC
        LIMIT 500";

$trips = db_fetch_all($sql, $params);

// Get devices for filter
$devices = device_list_all($tenantId);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-route me-2"></i>Trips</h1>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="device_id" class="form-label">Device</label>
                <select class="form-select" id="device_id" name="device_id">
                    <option value="">All Devices</option>
                    <?php foreach ($devices as $device): ?>
                    <option value="<?= $device['id'] ?>" <?= $deviceId === $device['id'] ? 'selected' : '' ?>>
                        <?= h($device['device_uid']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= h($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= h($endDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($trips)): ?>
        <div class="text-center py-5">
            <i class="fas fa-route fa-3x text-muted mb-3"></i>
            <p class="text-muted">No trips found for the selected criteria</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Asset</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Distance</th>
                        <th>Max Speed</th>
                        <th>Avg Speed</th>
                        <th>Idle Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td><?= h($trip['device_uid']) ?></td>
                        <td><?= h($trip['asset_name'] ?? '-') ?></td>
                        <td><?= format_datetime($trip['start_time']) ?></td>
                        <td><?= $trip['end_time'] ? format_datetime($trip['end_time']) : '<span class="badge bg-info">Active</span>' ?></td>
                        <td><?= $trip['duration_minutes'] ? $trip['duration_minutes'] . ' min' : '-' ?></td>
                        <td><?= $trip['distance_km'] ? number_format($trip['distance_km'], 2) . ' km' : '-' ?></td>
                        <td><?= $trip['max_speed'] ? number_format($trip['max_speed'], 1) . ' km/h' : '-' ?></td>
                        <td><?= $trip['avg_speed'] ? number_format($trip['avg_speed'], 1) . ' km/h' : '-' ?></td>
                        <td><?= $trip['idle_time_minutes'] ? $trip['idle_time_minutes'] . ' min' : '-' ?></td>
                        <td>
                            <?php if ($trip['end_time']): ?>
                            <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                            <span class="badge bg-info">In Progress</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewTrip(<?= $trip['id'] ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewTrip(tripId) {
    window.location.href = '/map.php?trip=' + tripId;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

