<?php

/**
 * Alerts Page
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/alerts.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

require_auth();
require_role('ReadOnly');

$title = 'Alerts - Dragnet Intelematics';
$showNav = true;
$tenantId = require_tenant();

$filter = input('filter', 'all');
$filters = [];

if ($filter === 'unacknowledged') {
    $filters['acknowledged'] = false;
} elseif ($filter === 'critical') {
    $filters['severity'] = 'critical';
}

$alerts = alert_list_all($filters, $tenantId);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-bell me-2"></i>Alerts</h1>
    </div>
    <div class="col-auto">
        <div class="btn-group">
            <a href="?filter=all" class="btn btn-outline-primary <?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="?filter=unacknowledged" class="btn btn-outline-warning <?= $filter === 'unacknowledged' ? 'active' : '' ?>">Unacknowledged</a>
            <a href="?filter=critical" class="btn btn-outline-danger <?= $filter === 'critical' ? 'active' : '' ?>">Critical</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Message</th>
                    <th>Device</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alerts)): ?>
                <tr>
                    <td colspan="7" class="text-center">No alerts found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($alerts as $alert): ?>
                <tr>
                    <td><?= h($alert['type']) ?></td>
                    <td>
                        <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : 'warning' ?>">
                            <?= h($alert['severity']) ?>
                        </span>
                    </td>
                    <td><?= h($alert['message'] ?? '-') ?></td>
                    <td><?= h($alert['device_uid'] ?? $alert['device_id']) ?></td>
                    <td><?= format_datetime($alert['created_at']) ?></td>
                    <td>
                        <?php if ($alert['acknowledged']): ?>
                            <span class="badge bg-success">Acknowledged</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$alert['acknowledged']): ?>
                            <button class="btn btn-sm btn-success" onclick="acknowledgeAlert(<?= $alert['id'] ?>)">
                                <i class="fas fa-check"></i>
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

<script>
function acknowledgeAlert(id) {
    $.post('/api/alerts/acknowledge.php', { id: id }, function() {
        location.reload();
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

