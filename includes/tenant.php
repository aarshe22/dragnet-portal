<?php

/**
 * Tenant Functions (Procedural)
 * Tenant context and isolation
 */

/**
 * Get current tenant context from session
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
        'Developer' => 5, // Top-level role with all capabilities
    ];
    
    $userLevel = $hierarchy[$context['user_role']] ?? 0;
    $requiredLevel = $hierarchy[$role] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Require minimum role level (exits with 403 if not met)
 * Note: Developer role has access to all roles (highest level)
 */
function require_role(string $role): void
{
    $context = get_tenant_context();
    if (!$context) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    
    // Developer role has access to everything
    if ($context['user_role'] === 'Developer') {
        return;
    }
    
    if (!has_role($role)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

/**
 * Add tenant WHERE clause to SQL
 */
function sql_tenant_where(?int $tenantId = null): string
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    return "tenant_id = :tenant_id";
}

/**
 * Add tenant parameter to params array
 */
function sql_tenant_params(?int $tenantId = null): array
{
    if ($tenantId === null) {
        $tenantId = require_tenant();
    }
    return ['tenant_id' => $tenantId];
}

