<?php

namespace DragNet\Core;

/**
 * Tenant Context
 * 
 * Represents the current tenant and user context for request isolation.
 */
class TenantContext
{
    private int $tenantId;
    private int $userId;
    private string $userEmail;
    private string $userRole;
    
    public function __construct(int $tenantId, int $userId, string $userEmail, string $userRole)
    {
        $this->tenantId = $tenantId;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->userRole = $userRole;
    }
    
    public function getTenantId(): int
    {
        return $this->tenantId;
    }
    
    public function getUserId(): int
    {
        return $this->userId;
    }
    
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
    
    public function getUserRole(): string
    {
        return $this->userRole;
    }
    
    public function hasRole(string $role): bool
    {
        $hierarchy = [
            'Guest' => 0,
            'ReadOnly' => 1,
            'Operator' => 2,
            'Administrator' => 3,
            'TenantOwner' => 4,
        ];
        
        $userLevel = $hierarchy[$this->userRole] ?? 0;
        $requiredLevel = $hierarchy[$role] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    public static function fromSession(): ?self
    {
        if (!Session::has('tenant_id') || !Session::has('user_id')) {
            return null;
        }
        
        return new self(
            Session::get('tenant_id'),
            Session::get('user_id'),
            Session::get('user_email', ''),
            Session::get('user_role', 'Guest')
        );
    }
    
    public function toSession(): void
    {
        Session::set('tenant_id', $this->tenantId);
        Session::set('user_id', $this->userId);
        Session::set('user_email', $this->userEmail);
        Session::set('user_role', $this->userRole);
    }
}

