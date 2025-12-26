<?php

/**
 * Geofences Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Geofences - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$geofences = db_fetch_all(
    "SELECT * FROM geofences WHERE tenant_id = :tenant_id ORDER BY name ASC",
    ['tenant_id' => $tenantId]
);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-draw-polygon me-2"></i>Geofences</h1>
    </div>
    <?php if (has_role('Operator')): ?>
    <div class="col-auto">
        <a href="/map.php" class="btn btn-success me-2">
            <i class="fas fa-draw-polygon me-1"></i>Draw New Geofence
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($geofences)): ?>
        <div class="text-center py-5">
            <i class="fas fa-draw-polygon fa-3x text-muted mb-3"></i>
            <p class="text-muted">No geofences configured</p>
            <?php if (has_role('Operator')): ?>
            <a href="/map.php" class="btn btn-success">
                <i class="fas fa-draw-polygon me-1"></i>Draw First Geofence
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($geofences as $gf): 
                        $coordinates = json_decode($gf['coordinates'], true);
                        $coordCount = is_array($coordinates) ? count($coordinates) : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?= h($gf['name']) ?></strong>
                            <?php if ($coordCount > 0): ?>
                            <br><small class="text-muted"><?= $coordCount ?> point<?= $coordCount !== 1 ? 's' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-info"><?= h(ucfirst($gf['type'])) ?></span></td>
                        <td>
                            <span class="badge bg-<?= $gf['active'] ? 'success' : 'secondary' ?>">
                                <?= $gf['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= format_datetime($gf['created_at']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewGeofence(<?= $gf['id'] ?>)" title="View on Map">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewGeofenceAnalytics(<?= $gf['id'] ?>)" title="View Analytics">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <?php if (has_role('Operator')): ?>
                            <button class="btn btn-sm btn-warning" onclick="editGeofence(<?= $gf['id'] ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-<?= $gf['active'] ? 'secondary' : 'success' ?>" onclick="toggleGeofence(<?= $gf['id'] ?>, <?= $gf['active'] ? 'false' : 'true' ?>)" title="<?= $gf['active'] ? 'Deactivate' : 'Activate' ?>">
                                <i class="fas fa-<?= $gf['active'] ? 'pause' : 'play' ?>"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteGeofence(<?= $gf['id'] ?>)" title="Delete">
                                <i class="fas fa-trash"></i>
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

<!-- Edit Geofence Modal -->
<?php if (has_role('Operator')): ?>
<div class="modal fade" id="editGeofenceModal" tabindex="-1" aria-labelledby="editGeofenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGeofenceModalLabel"><i class="fas fa-edit me-2"></i>Edit Geofence</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editGeofenceForm">
                    <input type="hidden" id="editGeofenceId" name="id">
                    
                    <div class="mb-3">
                        <label for="editGeofenceName" class="form-label">Geofence Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editGeofenceName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Associate with Devices</label>
                        <select class="form-select" id="editGeofenceDevices" name="device_ids[]" multiple size="5">
                            <option value="">Loading devices...</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple devices</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Associate with Device Groups</label>
                        <select class="form-select" id="editGeofenceGroups" name="group_ids[]" multiple size="5">
                            <option value="">Loading groups...</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple groups</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editGeofenceActive" name="active">
                            <label class="form-check-label" for="editGeofenceActive">
                                Active (Geofence is enabled)
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> To modify the geofence shape, please delete and recreate it on the map.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEditGeofence()">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function viewGeofence(id) {
    window.location.href = '/map.php?geofence=' + id;
}

function editGeofence(id) {
    // Load geofence data
    $.get('/api/geofences.php?id=' + id, function(geofence) {
        $('#editGeofenceId').val(geofence.id);
        $('#editGeofenceName').val(geofence.name);
        $('#editGeofenceActive').prop('checked', geofence.active == 1);
        
        // Load devices and groups
        loadEditGeofenceDevices(geofence);
        loadEditGeofenceGroups(geofence);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editGeofenceModal'));
        modal.show();
    }).fail(function() {
        alert('Failed to load geofence data');
    });
}

function loadEditGeofenceDevices(geofence) {
    $.get('/api/devices/map', function(devices) {
        const select = $('#editGeofenceDevices');
        select.empty();
        const associatedDeviceIds = geofence.devices ? geofence.devices.map(d => d.id) : [];
        
        devices.forEach(function(device) {
            const selected = associatedDeviceIds.includes(device.id) ? 'selected' : '';
            select.append(`<option value="${device.id}" ${selected}>${escapeHtml(device.device_uid)}${device.device_type_label ? ' (' + escapeHtml(device.device_type_label) + ')' : ''}</option>`);
        });
    });
}

function loadEditGeofenceGroups(geofence) {
    $.get('/api/device_groups.php', function(groups) {
        const select = $('#editGeofenceGroups');
        select.empty();
        const associatedGroupIds = geofence.groups ? geofence.groups.map(g => g.id) : [];
        
        groups.forEach(function(group) {
            const selected = associatedGroupIds.includes(group.id) ? 'selected' : '';
            select.append(`<option value="${group.id}" ${selected}>${escapeHtml(group.name)}</option>`);
        });
    });
}

function saveEditGeofence() {
    const id = $('#editGeofenceId').val();
    if (!id) {
        alert('Geofence ID missing');
        return;
    }
    
    const form = $('#editGeofenceForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const name = $('#editGeofenceName').val();
    const deviceIds = Array.from($('#editGeofenceDevices option:selected')).map(opt => opt.value).filter(v => v);
    const groupIds = Array.from($('#editGeofenceGroups option:selected')).map(opt => opt.value).filter(v => v);
    
    const data = {
        name: name,
        device_ids: deviceIds,
        group_ids: groupIds,
        active: $('#editGeofenceActive').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: '/api/geofences.php?id=' + id,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('editGeofenceModal')).hide();
                location.reload();
            } else {
                alert('Failed to update geofence: ' + (response.error || 'Unknown error'));
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to update geofence';
            alert(error);
        }
    });
}

function toggleGeofence(id, active) {
    const action = active ? 'activate' : 'deactivate';
    if (!confirm(`Are you sure you want to ${action} this geofence?`)) {
        return;
    }
    
    $.ajax({
        url: '/api/geofences.php?id=' + id,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ active: active ? 1 : 0 }),
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to update geofence: ' + (response.error || 'Unknown error'));
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to update geofence';
            alert(error);
        }
    });
}

function deleteGeofence(id) {
    if (!confirm('Are you sure you want to delete this geofence? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: '/api/geofences.php?id=' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to delete geofence: ' + (response.error || 'Unknown error'));
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to delete geofence';
            alert(error);
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
