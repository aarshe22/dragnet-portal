SET FOREIGN_KEY_CHECKS = 0;

-- Alert Rules Table
CREATE TABLE IF NOT EXISTS `alert_rules` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('info','warning','critical') DEFAULT 'warning',
  `enabled` tinyint(1) DEFAULT 1,
  `threshold_value` decimal(10,2) DEFAULT NULL,
  `threshold_unit` varchar(20) DEFAULT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `notification_recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_enabled` (`enabled`),
  CONSTRAINT `fk_alert_rules_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert Rule-Device Associations Table
CREATE TABLE IF NOT EXISTS `alert_rule_devices` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rule_device` (`rule_id`, `device_id`),
  KEY `idx_rule` (`rule_id`),
  KEY `idx_device` (`device_id`),
  CONSTRAINT `fk_alert_rule_devices_rule_id` FOREIGN KEY (`rule_id`) REFERENCES `alert_rules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alert_rule_devices_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert Rule-Group Associations Table
CREATE TABLE IF NOT EXISTS `alert_rule_groups` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rule_group` (`rule_id`, `group_id`),
  KEY `idx_rule` (`rule_id`),
  KEY `idx_group` (`group_id`),
  CONSTRAINT `fk_alert_rule_groups_rule_id` FOREIGN KEY (`rule_id`) REFERENCES `alert_rules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alert_rule_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

