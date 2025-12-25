SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS user_invites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    role ENUM('Guest', 'ReadOnly', 'Operator', 'Administrator', 'TenantOwner') DEFAULT 'Guest',
    invited_by INT UNSIGNED NULL,
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    INDEX idx_accepted (accepted_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

