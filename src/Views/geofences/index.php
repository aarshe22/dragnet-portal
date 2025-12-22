<?php
$title = 'Geofences - DragNet Portal';
ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-draw-polygon me-2"></i>Geofences</h1>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-1"></i>New Geofence
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="geofences-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    loadGeofences();
});

function loadGeofences() {
    $.get('/api/geofences', function(geofences) {
        const tbody = $('#geofences-table tbody');
        if (geofences.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">No geofences found</td></tr>');
            return;
        }
        
        const html = geofences.map(gf => `
            <tr>
                <td>${gf.name}</td>
                <td>${gf.type}</td>
                <td>
                    <span class="badge bg-${gf.active ? 'success' : 'secondary'}">
                        ${gf.active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editGeofence(${gf.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteGeofence(${gf.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        tbody.html(html);
    });
}

function showCreateModal() {
    // Placeholder - would show modal for creating geofence
    alert('Geofence creation UI would go here');
}

function editGeofence(id) {
    // Placeholder
    alert('Geofence editing UI would go here');
}

function deleteGeofence(id) {
    if (confirm('Are you sure you want to delete this geofence?')) {
        $.ajax({
            url: `/api/geofences/${id}`,
            method: 'DELETE',
            success: function() {
                loadGeofences();
            }
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

