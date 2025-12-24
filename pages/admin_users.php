<?php

/**
 * Admin Users Management Page
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
require_role('Administrator');

$title = 'User Management - DragNet Portal';
$showNav = true;
$tenantId = require_tenant();

$users = db_fetch_all(
    "SELECT * FROM users WHERE tenant_id = :tenant_id ORDER BY email ASC",
    ['tenant_id' => $tenantId]
);

ob_start();
?>

<div class="row mb-3">
    <div class="col">
        <h1><i class="fas fa-users me-2"></i>User Management</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="4" class="text-center">No users found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= h($user['email']) ?></td>
                    <td><span class="badge bg-info"><?= h($user['role']) ?></span></td>
                    <td><?= format_datetime($user['last_login']) ?></td>
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

