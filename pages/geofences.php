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
        <button class="btn btn-primary" onclick="showGeofenceModal()">
            <i class="fas fa-plus me-1"></i>Add Geofence
        </button>
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
            <button class="btn btn-primary" onclick="showGeofenceModal()">
                <i class="fas fa-plus me-1"></i>Create First Geofence
            </button>
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
                    <?php foreach ($geofences as $gf): ?>
                    <tr>
                        <td><?= h($gf['name']) ?></td>
                        <td><span class="badge bg-info"><?= h($gf['type']) ?></span></td>
                        <td>
                            <span class="badge bg-<?= $gf['active'] ? 'success' : 'secondary' ?>">
                                <?= $gf['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= format_datetime($gf['created_at']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewGeofence(<?= $gf['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (has_role('Operator')): ?>
                            <button class="btn btn-sm btn-warning" onclick="editGeofence(<?= $gf['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteGeofence(<?= $gf['id'] ?>)">
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

<script>
function showGeofenceModal() {
    alert('Geofence creation feature coming soon');
}

function viewGeofence(id) {
    window.location.href = '/map.php?geofence=' + id;
}

function editGeofence(id) {
    alert('Geofence editing feature coming soon');
}

function deleteGeofence(id) {
    if (confirm('Are you sure you want to delete this geofence?')) {
        alert('Geofence deletion feature coming soon');
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>
