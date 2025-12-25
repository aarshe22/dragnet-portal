SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE IF NOT EXISTS email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(500),
    provider VARCHAR(50),
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    error_message TEXT,
    response_data JSON,
    debug_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_recipient (recipient),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1
