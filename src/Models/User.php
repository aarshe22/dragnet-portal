<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * User Model
 */
class User extends BaseModel
{
    protected string $table = 'users';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    /**
     * Find user by email and tenant
     */
    public function findByEmail(string $email, ?int $tenantId = null): ?array
    {
        $tenantId = $tenantId ?? $this->tenantId;
        if ($tenantId === null) {
            throw new \Exception('Tenant ID required');
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND tenant_id = :tenant_id";
        return $this->db->fetchOne($sql, ['email' => $email, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Find or create user from SSO
     */
    public function findOrCreateFromSSO(string $email, int $tenantId, string $provider, string $subject, string $role = 'Guest'): array
    {
        $user = $this->findByEmail($email, $tenantId);
        
        if ($user) {
            // Update last login
            $this->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s'),
                'sso_provider' => $provider,
                'sso_subject' => $subject
            ]);
            return $this->find($user['id']);
        }
        
        // Create new user
        $userId = $this->create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'role' => $role,
            'sso_provider' => $provider,
            'sso_subject' => $subject,
            'last_login' => date('Y-m-d H:i:s')
        ]);
        
        return $this->find($userId);
    }
}

