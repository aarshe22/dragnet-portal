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

$title = 'Assets - DragNet Portal';
$showNav = true;
$tenantId = require_tenant();

$assets = asset_list_all($tenantId);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-car me-2"></i>Assets</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
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
                <?php if (empty($assets)): ?>
                <tr>
                    <td colspan="5" class="text-center">No assets found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?= h($asset['name']) ?></td>
                    <td><?= h($asset['vehicle_id'] ?? '-') ?></td>
                    <td><?= h($asset['device_uid'] ?? '-') ?></td>
                    <td>
                        <span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= h($asset['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/assets/detail.php?id=<?= $asset['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
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

