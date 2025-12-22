<?php

namespace DragNet\Core;

/**
 * Application Container
 * 
 * Holds configuration, database connection, and tenant context.
 */
class Application
{
    private array $config;
    private Database $db;
    private ?TenantContext $tenantContext = null;
    
    public function __construct(array $config, Database $db)
    {
        $this->config = $config;
        $this->db = $db;
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function getDatabase(): Database
    {
        return $this->db;
    }
    
    public function setTenantContext(TenantContext $context): void
    {
        $this->tenantContext = $context;
    }
    
    public function getTenantContext(): ?TenantContext
    {
        return $this->tenantContext;
    }
    
    public function getTenantId(): ?int
    {
        return $this->tenantContext?->getTenantId();
    }
    
    public function requireTenant(): int
    {
        if (!$this->tenantContext) {
            throw new \Exception('Tenant context required');
        }
        return $this->tenantContext->getTenantId();
    }
}

