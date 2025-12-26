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

$title = 'Asset Detail - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();
$canEdit = has_role('Operator');

$assetId = (int)($_GET['id'] ?? 0);
$asset = asset_find_with_devices($assetId, $tenantId);

if (!$asset) {
    http_response_code(404);
    echo 'Asset not found';
    exit;
}

$linkedDevices = $asset['devices'] ?? [];
$unlinkedDevices = asset_get_unlinked_devices($tenantId);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Asset: <?= h($asset['name']) ?></h1>
    </div>
    <div class="col-auto">
        <a href="/assets.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Assets
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Asset Information</h5>
                <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-secondary" onclick="showAssetModal(<?= $asset['id'] ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <?php endif; ?>
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
                            <span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : ($asset['status'] === 'maintenance' ? 'warning' : 'secondary') ?>">
                                <?= h($asset['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Linked Devices:</th>
                        <td>
                            <span class="badge bg-info"><?= count($linkedDevices) ?> device(s)</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Linked Devices</h5>
                <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" onclick="showLinkDeviceModal()">
                    <i class="fas fa-link me-2"></i>Link Devices
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($linkedDevices)): ?>
                    <p class="text-muted mb-0">No devices linked to this asset.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Device UID</th>
                                    <th>IMEI</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linkedDevices as $device): ?>
                                <tr>
                                    <td>
                                        <a href="/devices/detail.php?id=<?= $device['id'] ?>">
                                            <?= h($device['device_uid']) ?>
                                        </a>
                                    </td>
                                    <td><?= h($device['imei']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $device['status'] === 'online' || $device['status'] === 'moving' ? 'success' : 
                                            ($device['status'] === 'idle' ? 'warning' : 
                                            ($device['status'] === 'parked' ? 'secondary' : 'danger')) 
                                        ?>">
                                            <?= h($device['status'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/devices/detail.php?id=<?= $device['id'] ?>" class="btn btn-sm btn-primary" title="View Device">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($canEdit): ?>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="unlinkDevice(<?= $device['id'] ?>)" title="Unlink Device">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
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
        <?php if (!empty($linkedDevices)): ?>
            <?php
            // Get latest telemetry for the first device (or most recent)
            $latestTelemetry = null;
            $latestDevice = null;
            foreach ($linkedDevices as $device) {
                $telemetry = device_get_latest_telemetry($device['id']);
                if ($telemetry && (!$latestTelemetry || strtotime($telemetry['timestamp']) > strtotime($latestTelemetry['timestamp']))) {
                    $latestTelemetry = $telemetry;
                    $latestDevice = $device;
                }
            }
            ?>
            <?php if ($latestTelemetry): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Latest Position (<?= h($latestDevice['device_uid']) ?>)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Latitude:</th>
                            <td><?= number_format($latestTelemetry['lat'], 6) ?></td>
                        </tr>
                        <tr>
                            <th>Longitude:</th>
                            <td><?= number_format($latestTelemetry['lon'], 6) ?></td>
                        </tr>
                        <tr>
                            <th>Speed:</th>
                            <td><?= $latestTelemetry['speed'] !== null ? number_format($latestTelemetry['speed'], 1) . ' km/h' : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Last Update:</th>
                            <td><?= format_datetime($latestTelemetry['timestamp']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p class="text-muted">No position data available. Link devices to see telemetry data.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Asset Edit Modal -->
<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assetForm">
                    <input type="hidden" id="assetId" name="id" value="<?= $asset['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="assetName" name="name" value="<?= h($asset['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vehicle ID</label>
                        <input type="text" class="form-control" id="assetVehicleId" name="vehicle_id" value="<?= h($asset['vehicle_id'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="assetStatus" name="status" required>
                            <option value="active" <?= $asset['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $asset['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="maintenance" <?= $asset['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAsset">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Link Device Modal -->
<div class="modal fade" id="linkDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Link Devices to Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($unlinkedDevices)): ?>
                    <p class="text-muted">No unlinked devices available. All devices are already linked to assets.</p>
                <?php else: ?>
                    <p class="text-muted mb-3">Select one or more devices to link to this asset:</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllDevices" onchange="toggleAllDevices()">
                                    </th>
                                    <th>Device UID</th>
                                    <th>IMEI</th>
                                    <th>Model</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unlinkedDevices as $device): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="device-checkbox" value="<?= $device['id'] ?>" name="device_ids[]">
                                    </td>
                                    <td><?= h($device['device_uid']) ?></td>
                                    <td><?= h($device['imei']) ?></td>
                                    <td><?= h($device['model'] ?? 'FMM13A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $device['status'] === 'online' || $device['status'] === 'moving' ? 'success' : 
                                            ($device['status'] === 'idle' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= h($device['status'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnLinkDevices" onclick="linkDevices()" <?= empty($unlinkedDevices) ? 'disabled' : '' ?>>Link Selected Devices</button>
            </div>
        </div>
    </div>
</div>

<script>
function showAssetModal() {
    new bootstrap.Modal(document.getElementById('assetModal')).show();
}

function saveAsset() {
    const data = {
        id: parseInt($('#assetId').val()),
        name: $('#assetName').val(),
        vehicle_id: $('#assetVehicleId').val() || null,
        status: $('#assetStatus').val()
    };
    
    $.ajax({
        url: '/api/assets.php',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function() {
            bootstrap.Modal.getInstance(document.getElementById('assetModal')).hide();
            location.reload();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Unknown error';
            alert('Error: ' + error);
        }
    });
}

function showLinkDeviceModal() {
    new bootstrap.Modal(document.getElementById('linkDeviceModal')).show();
}

function toggleAllDevices() {
    const selectAll = document.getElementById('selectAllDevices');
    const checkboxes = document.querySelectorAll('.device-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function linkDevices() {
    const selectedDevices = Array.from(document.querySelectorAll('.device-checkbox:checked')).map(cb => parseInt(cb.value));
    
    if (selectedDevices.length === 0) {
        alert('Please select at least one device');
        return;
    }
    
    const data = {
        asset_id: <?= $asset['id'] ?>,
        device_ids: selectedDevices
    };
    
    $.ajax({
        url: '/api/assets/devices.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            bootstrap.Modal.getInstance(document.getElementById('linkDeviceModal')).hide();
            location.reload();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Unknown error';
            alert('Error: ' + error);
        }
    });
}

function unlinkDevice(deviceId) {
    if (!confirm('Are you sure you want to unlink this device from the asset?')) {
        return;
    }
    
    const data = {
        device_ids: [deviceId]
    };
    
    $.ajax({
        url: '/api/assets/devices.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function() {
            location.reload();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Unknown error';
            alert('Error: ' + error);
        }
    });
}

$(document).ready(function() {
    $('#btnSaveAsset').on('click', saveAsset);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

