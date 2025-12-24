<?php

/**
 * User Profile Page
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

$title = 'Profile - DragNet Portal';
$context = get_tenant_context();

$user = db_fetch_one(
    "SELECT u.*, t.name as tenant_name FROM users u LEFT JOIN tenants t ON u.tenant_id = t.id WHERE u.id = :id",
    ['id' => $context['user_id']]
);

ob_start();
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>User Profile</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">Email:</th>
                        <td><?= h($user['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Tenant:</th>
                        <td><?= h($user['tenant_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-info"><?= h($user['role']) ?></span></td>
                    </tr>
                    <tr>
                        <th>Last Login:</th>
                        <td><?= format_datetime($user['last_login']) ?></td>
                    </tr>
                    <tr>
                        <th>SSO Provider:</th>
                        <td><?= h($user['sso_provider'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Account Created:</th>
                        <td><?= format_datetime($user['created_at']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../views/layout.php';
?>

