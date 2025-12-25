CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    applied_by INT UNSIGNED NULL,
    execution_time DECIMAL(10, 3) NULL,
    error_message TEXT NULL,
    status ENUM('success', 'failed', 'partial') DEFAULT 'success',
    INDEX idx_filename (filename),
    INDEX idx_applied_at (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
