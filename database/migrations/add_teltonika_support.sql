-- Migration: Add Teltonika FMM13A support
-- Adds device type and Teltonika-specific fields

ALTER TABLE devices 
ADD COLUMN device_type VARCHAR(50) DEFAULT 'generic' AFTER device_uid,
ADD COLUMN protocol VARCHAR(50) DEFAULT 'http' AFTER device_type,
ADD COLUMN teltonika_codec TINYINT NULL AFTER protocol,
ADD INDEX idx_device_type (device_type),
ADD INDEX idx_imei (imei);

-- Update existing devices to generic type
UPDATE devices SET device_type = 'generic' WHERE device_type IS NULL;

-- Add IO elements storage to telemetry (for Teltonika-specific data)
ALTER TABLE telemetry
ADD COLUMN io_elements JSON NULL AFTER temperature,
ADD COLUMN satellites TINYINT NULL AFTER altitude,
ADD COLUMN priority TINYINT NULL AFTER satellites;

-- Add device configuration table for Teltonika devices
CREATE TABLE IF NOT EXISTS device_configurations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id INT UNSIGNED NOT NULL,
    config_key VARCHAR(100) NOT NULL,
    config_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    UNIQUE KEY uk_device_config (device_id, config_key),
    INDEX idx_device (device_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

