<?php

/**
 * Authentication Functions (Procedural)
 * SSO authentication and user management
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Find user by email and tenant
 */
function user_find_by_email(string $email, int $tenantId): ?array
{
    $sql = "SELECT * FROM users WHERE email = :email AND tenant_id = :tenant_id";
    return db_fetch_one($sql, ['email' => $email, 'tenant_id' => $tenantId]);
}

/**
 * Find or create user from SSO
 */
function user_find_or_create_from_sso(string $email, int $tenantId, string $provider, string $subject, string $role = 'Guest'): array
{
    $user = user_find_by_email($email, $tenantId);
    
    if ($user) {
        // Update last login
        db_execute(
            "UPDATE users SET last_login = NOW(), sso_provider = :provider, sso_subject = :subject WHERE id = :id",
            [
                'id' => $user['id'],
                'provider' => $provider,
                'subject' => $subject
            ]
        );
        return user_find_by_email($email, $tenantId);
    }
    
    // Create new user
    db_execute(
        "INSERT INTO users (tenant_id, email, role, sso_provider, sso_subject, last_login) 
         VALUES (:tenant_id, :email, :role, :provider, :subject, NOW())",
        [
            'tenant_id' => $tenantId,
            'email' => $email,
            'role' => $role,
            'provider' => $provider,
            'subject' => $subject
        ]
    );
    
    $userId = db_last_insert_id();
    return db_fetch_one("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
}

/**
 * Check if user is authenticated
 */
function is_authenticated(): bool
{
    return get_tenant_context() !== null;
}

/**
 * Require authentication (redirects to login if not authenticated)
 */
function require_auth(): void
{
    if (!is_authenticated()) {
        header('Location: /login.php');
        exit;
    }
}

