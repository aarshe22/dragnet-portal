<?php

/**
 * User Invite Functions (Procedural)
 * Handles user invitation system
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tenant.php';

/**
 * Create a user invitation
 */
function invite_create(int $tenantId, string $email, string $role = 'Guest', ?int $invitedBy = null, int $expiresInDays = 7): array
{
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    
    // Check if token already exists (very unlikely)
    $existing = db_fetch_one("SELECT id FROM user_invites WHERE token = :token", ['token' => $token]);
    $attempts = 0;
    while ($existing && $attempts < 10) {
        $token = bin2hex(random_bytes(32));
        $existing = db_fetch_one("SELECT id FROM user_invites WHERE token = :token", ['token' => $token]);
        $attempts++;
    }
    
    // Check if user already exists
    $existingUser = db_fetch_one(
        "SELECT id FROM users WHERE email = :email AND tenant_id = :tenant_id",
        ['email' => $email, 'tenant_id' => $tenantId]
    );
    
    if ($existingUser) {
        throw new Exception('User with this email already exists');
    }
    
    // Check for pending invite
    $pendingInvite = db_fetch_one(
        "SELECT id FROM user_invites WHERE email = :email AND tenant_id = :tenant_id AND accepted_at IS NULL AND expires_at > NOW()",
        ['email' => $email, 'tenant_id' => $tenantId]
    );
    
    if ($pendingInvite) {
        throw new Exception('Pending invitation already exists for this email');
    }
    
    // Calculate expiration
    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
    
    // Create invite
    db_execute(
        "INSERT INTO user_invites (tenant_id, email, token, role, invited_by, expires_at) 
         VALUES (:tenant_id, :email, :token, :role, :invited_by, :expires_at)",
        [
            'tenant_id' => $tenantId,
            'email' => $email,
            'token' => $token,
            'role' => $role,
            'invited_by' => $invitedBy,
            'expires_at' => $expiresAt
        ]
    );
    
    $inviteId = db_last_insert_id();
    return db_fetch_one("SELECT * FROM user_invites WHERE id = :id", ['id' => $inviteId]);
}

/**
 * Get invite by token
 */
function invite_find_by_token(string $token): ?array
{
    return db_fetch_one(
        "SELECT i.*, t.name as tenant_name 
         FROM user_invites i 
         LEFT JOIN tenants t ON i.tenant_id = t.id 
         WHERE i.token = :token",
        ['token' => $token]
    );
}

/**
 * Accept invitation and create user
 */
function invite_accept(string $token, string $ssoProvider = 'invite', string $ssoSubject = null): array
{
    $invite = invite_find_by_token($token);
    
    if (!$invite) {
        throw new Exception('Invalid invitation token');
    }
    
    if ($invite['accepted_at']) {
        throw new Exception('Invitation has already been accepted');
    }
    
    if (strtotime($invite['expires_at']) < time()) {
        throw new Exception('Invitation has expired');
    }
    
    // Check if user already exists
    $existingUser = db_fetch_one(
        "SELECT * FROM users WHERE email = :email AND tenant_id = :tenant_id",
        ['email' => $invite['email'], 'tenant_id' => $invite['tenant_id']]
    );
    
    if ($existingUser) {
        // User already exists - update role if different and mark invite as accepted
        if ($existingUser['role'] !== $invite['role']) {
            db_execute(
                "UPDATE users SET role = :role WHERE id = :id",
                ['role' => $invite['role'], 'id' => $existingUser['id']]
            );
            $existingUser['role'] = $invite['role'];
        }
        
        // Mark invite as accepted
        db_execute(
            "UPDATE user_invites SET accepted_at = NOW() WHERE id = :id",
            ['id' => $invite['id']]
        );
        
        // Return existing user data
        return [
            'id' => $existingUser['id'],
            'tenant_id' => $existingUser['tenant_id'],
            'email' => $existingUser['email'],
            'role' => $existingUser['role']
        ];
    }
    
    // Generate SSO subject if not provided
    if (!$ssoSubject) {
        $ssoSubject = 'invite_' . $token;
    }
    
    // Create user
    db_execute(
        "INSERT INTO users (tenant_id, email, role, sso_provider, sso_subject, last_login) 
         VALUES (:tenant_id, :email, :role, :sso_provider, :sso_subject, NOW())",
        [
            'tenant_id' => $invite['tenant_id'],
            'email' => $invite['email'],
            'role' => $invite['role'],
            'sso_provider' => $ssoProvider,
            'sso_subject' => $ssoSubject
        ]
    );
    
    $userId = db_last_insert_id();
    
    // Mark invite as accepted
    db_execute(
        "UPDATE user_invites SET accepted_at = NOW() WHERE id = :id",
        ['id' => $invite['id']]
    );
    
    // Get created user
    $user = db_fetch_one("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
    
    return $user;
}

/**
 * Get all invites for a tenant
 */
function invite_list_all(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    return db_fetch_all(
        "SELECT i.*, u.email as invited_by_email 
         FROM user_invites i 
         LEFT JOIN users u ON i.invited_by = u.id 
         WHERE i.tenant_id = :tenant_id 
         ORDER BY i.created_at DESC",
        ['tenant_id' => $tenantId]
    );
}

/**
 * Delete invite
 */
function invite_delete(int $inviteId, ?int $tenantId = null): bool
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $affected = db_execute(
        "DELETE FROM user_invites WHERE id = :id AND tenant_id = :tenant_id",
        ['id' => $inviteId, 'tenant_id' => $tenantId]
    );
    
    return $affected > 0;
}

/**
 * Resend invitation email
 */
function invite_resend(int $inviteId, ?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    
    $invite = db_fetch_one(
        "SELECT * FROM user_invites WHERE id = :id AND tenant_id = :tenant_id",
        ['id' => $inviteId, 'tenant_id' => $tenantId]
    );
    
    if (!$invite) {
        throw new Exception('Invitation not found');
    }
    
    if ($invite['accepted_at']) {
        throw new Exception('Invitation has already been accepted');
    }
    
    // Return invite data for email sending
    return $invite;
}

