<?php

/**
 * Authentication Functions (Procedural)
 */

/**
 * Get current tenant context
 */
function get_tenant_context(): ?array
{
    if (!session_has('tenant_id') || !session_has('user_id')) {
        return null;
    }
    
    return [
        'tenant_id' => session_get('tenant_id'),
        'user_id' => session_get('user_id'),
        'user_email' => session_get('user_email', ''),
        'user_role' => session_get('user_role', 'Guest'),
    ];
}

/**
 * Set tenant context in session
 */
function set_tenant_context(array $context): void
{
    session_set('tenant_id', $context['tenant_id']);
    session_set('user_id', $context['user_id']);
    session_set('user_email', $context['user_email']);
    session_set('user_role', $context['user_role']);
}

/**
 * Require tenant context (throws if not set)
 */
function require_tenant(): int
{
    $context = get_tenant_context();
    if (!$context) {
        throw new Exception('Tenant context required');
    }
    return $context['tenant_id'];
}

/**
 * Check if user has required role
 */
function has_role(string $role): bool
{
    $context = get_tenant_context();
    if (!$context) {
        return false;
    }
    
    $hierarchy = [
        'Guest' => 0,
        'ReadOnly' => 1,
        'Operator' => 2,
        'Administrator' => 3,
        'TenantOwner' => 4,
    ];
    
    $userLevel = $hierarchy[$context['user_role']] ?? 0;
    $requiredLevel = $hierarchy[$role] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Require minimum role level
 */
function require_role(string $role): void
{
    if (!has_role($role)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

