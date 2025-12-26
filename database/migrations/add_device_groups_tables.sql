SET FOREIGN_KEY_CHECKS = 0;

-- Device Groups Table
CREATE TABLE IF NOT EXISTS `device_groups` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_active` (`active`),
  CONSTRAINT `fk_device_groups_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Device Group Members Table
CREATE TABLE IF NOT EXISTS `device_group_members` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_device` (`group_id`, `device_id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_device` (`device_id`),
  CONSTRAINT `fk_device_group_members_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_device_group_members_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

