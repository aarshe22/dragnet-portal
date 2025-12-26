<?php

/**
 * Assets List Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/assets.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Assets - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();
$canEdit = has_role('Operator');

$assets = asset_list_all($tenantId);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Assets</h1>
    </div>
    <?php if ($canEdit): ?>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" onclick="showAssetModal()">
            <i class="fas fa-plus me-2"></i>Add Asset
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Vehicle ID</th>
                    <th>Devices</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assets)): ?>
                <tr>
                    <td colspan="5" class="text-center">No assets found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?= h($asset['name']) ?></td>
                    <td><?= h($asset['vehicle_id'] ?? '-') ?></td>
                    <td>
                        <?php if ($asset['device_count'] > 0): ?>
                            <span class="badge bg-info"><?= $asset['device_count'] ?> device(s)</span>
                            <?php if ($asset['device_uids']): ?>
                                <small class="text-muted d-block"><?= h($asset['device_uids']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">No devices</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : ($asset['status'] === 'maintenance' ? 'warning' : 'secondary') ?>">
                            <?= h($asset['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/assets/detail.php?id=<?= $asset['id'] ?>" class="btn btn-sm btn-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="showAssetModal(<?= $asset['id'] ?>)" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAsset(<?= $asset['id'] ?>)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Asset Modal -->
<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetModalTitle">Add Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assetForm">
                    <input type="hidden" id="assetId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="assetName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vehicle ID</label>
                        <input type="text" class="form-control" id="assetVehicleId" name="vehicle_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="assetStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
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

<script>
function showAssetModal(id = null) {
    $('#assetModalTitle').text(id ? 'Edit Asset' : 'Add Asset');
    $('#assetForm')[0].reset();
    $('#assetId').val(id || '');
    
    if (id) {
        $.get('/api/assets.php?id=' + id, function(asset) {
            if (asset) {
                $('#assetName').val(asset.name);
                $('#assetVehicleId').val(asset.vehicle_id || '');
                $('#assetStatus').val(asset.status || 'active');
            }
        }).fail(function() {
            alert('Error loading asset');
        });
    }
    
    new bootstrap.Modal(document.getElementById('assetModal')).show();
}

function saveAsset() {
    const data = {
        name: $('#assetName').val(),
        vehicle_id: $('#assetVehicleId').val() || null,
        status: $('#assetStatus').val()
    };
    
    const id = $('#assetId').val();
    const url = '/api/assets.php';
    const method = id ? 'PUT' : 'POST';
    
    if (id) {
        data.id = parseInt(id);
    }
    
    $.ajax({
        url: url,
        method: method,
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

function deleteAsset(id) {
    if (!confirm('Are you sure you want to delete this asset? This will unlink all devices from the asset.')) {
        return;
    }
    
    $.ajax({
        url: '/api/assets.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
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

