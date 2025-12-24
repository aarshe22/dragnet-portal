<?php

/**
 * SSO Callback Handler
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$config = $GLOBALS['config'];
db_init($config['database']);
session_start_custom($config['session']);

$email = input('email');
$tenantId = (int)input('tenant_id', 1);
$provider = input('provider', 'dev');

// For development/test logins
if ($provider === 'dev' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $email)) {
    if (!$email) {
        redirect('/login.php?error=email_required');
    }
    
    // Verify tenant exists, create if missing (development mode)
    $tenant = db_fetch_one("SELECT id FROM tenants WHERE id = :id", ['id' => $tenantId]);
    
    if (!$tenant) {
        // In development mode (dev provider), auto-create tenant
        try {
            db_execute(
                "INSERT INTO tenants (id, name, region) VALUES (:id, :name, 'us-east')",
                ['id' => $tenantId, 'name' => 'Development Tenant ' . $tenantId]
            );
            $tenant = db_fetch_one("SELECT id FROM tenants WHERE id = :id", ['id' => $tenantId]);
        } catch (Exception $e) {
            // If insert fails (e.g., tenant already exists from another request), try to fetch again
            $tenant = db_fetch_one("SELECT id FROM tenants WHERE id = :id", ['id' => $tenantId]);
            if (!$tenant) {
                redirect('/login.php?error=tenant_not_found&details=' . urlencode($e->getMessage()));
            }
        }
    }
    
    // For dev mode, set default role to Administrator for first user
    $existingUser = user_find_by_email($email, $tenantId);
    $role = $existingUser ? $existingUser['role'] : 'Administrator';
    
    $user = user_find_or_create_from_sso($email, $tenantId, 'dev', 'dev_' . $email, $role);
    
    // Create tenant context
    set_tenant_context([
        'tenant_id' => $tenantId,
        'user_id' => $user['id'],
        'user_email' => $user['email'],
        'user_role' => $user['role'],
    ]);
    
    redirect('/dashboard.php');
}

// Production SSO callback would go here
redirect('/login.php?error=invalid_callback');

