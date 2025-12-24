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

$title = 'Geofences - DragNet Portal';
$tenantId = require_tenant();

$geofences = db_fetch_all(
    "SELECT * FROM geofences WHERE tenant_id = :tenant_id AND active = 1 ORDER BY name ASC",
    ['tenant_id' => $tenantId]
);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-draw-polygon me-2"></i>Geofences</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($geofences)): ?>
                <tr>
                    <td colspan="4" class="text-center">No geofences found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($geofences as $gf): ?>
                <tr>
                    <td><?= h($gf['name']) ?></td>
                    <td><?= h($gf['type']) ?></td>
                    <td>
                        <span class="badge bg-<?= $gf['active'] ? 'success' : 'secondary' ?>">
                            <?= $gf['active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </button>
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

