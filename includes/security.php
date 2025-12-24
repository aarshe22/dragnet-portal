<?php

/**
 * Security Functions (Procedural)
 * Production-ready security utilities
 */

/**
 * Sanitize input for database
 */
function sanitize_input($input): string
{
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate IMEI format
 */
function validate_imei(string $imei): bool
{
    // IMEI should be 15 digits
    return preg_match('/^\d{15}$/', $imei) === 1;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token(): string
{
    if (!session_has('csrf_token')) {
        session_set('csrf_token', bin2hex(random_bytes(32)));
    }
    return session_get('csrf_token');
}

/**
 * Verify CSRF token
 */
function verify_csrf_token(string $token): bool
{
    return session_has('csrf_token') && hash_equals(session_get('csrf_token'), $token);
}

/**
 * Rate limiting check
 */
function check_rate_limit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $cacheKey = "rate_limit_{$key}";
    $attempts = session_get($cacheKey, []);
    
    // Remove old attempts outside the window
    $now = time();
    $attempts = array_filter($attempts, function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });
    
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    $attempts[] = $now;
    session_set($cacheKey, $attempts);
    
    return true;
}

/**
 * Log security event
 */
function log_security_event(string $event, array $details = []): void
{
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/tenant.php';
    
    $context = get_tenant_context();
    $tenantId = $context ? $context['tenant_id'] : null;
    $userId = $context ? $context['user_id'] : null;
    
    try {
        db_execute(
            "INSERT INTO audit_log (tenant_id, user_id, action, details, ip_address, user_agent) 
             VALUES (:tenant_id, :user_id, :action, :details, :ip_address, :user_agent)",
            [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'action' => $event,
                'details' => json_encode($details),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        );
    } catch (Exception $e) {
        error_log('Failed to log security event: ' . $e->getMessage());
    }
}

/**
 * Check if IP is allowed
 */
function is_ip_allowed(string $ip): bool
{
    // In production, implement IP whitelist/blacklist logic
    return true;
}

