<?php
$title = 'Assets - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Assets</h1>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-1"></i>New Asset
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="assets-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Vehicle ID</th>
                    <th>Device</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetModalTitle">New Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assetForm">
                    <input type="hidden" id="asset_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="asset_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vehicle ID</label>
                        <input type="text" class="form-control" id="vehicle_id" name="vehicle_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="asset_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAsset()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadAssets();
});

function loadAssets() {
    $.get('/api/assets', function(assets) {
        const tbody = $('#assets-table tbody');
        if (assets.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center">No assets found</td></tr>');
            return;
        }
        
        const html = assets.map(asset => `
            <tr>
                <td>${asset.name}</td>
                <td>${asset.vehicle_id || '-'}</td>
                <td>${asset.device_uid || '-'}</td>
                <td><span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></td>
                <td>
                    <a href="/assets/${asset.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}

function showCreateModal() {
    $('#assetModalTitle').text('New Asset');
    $('#assetForm')[0].reset();
    $('#asset_id').val('');
    new bootstrap.Modal(document.getElementById('assetModal')).show();
}

function saveAsset() {
    const id = $('#asset_id').val();
    const data = {
        name: $('#asset_name').val(),
        vehicle_id: $('#vehicle_id').val(),
        status: $('#asset_status').val()
    };
    
    const url = id ? `/api/assets/${id}` : '/api/assets';
    const method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function() {
            bootstrap.Modal.getInstance(document.getElementById('assetModal')).hide();
            loadAssets();
        },
        error: function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.error || 'Unknown error'));
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

