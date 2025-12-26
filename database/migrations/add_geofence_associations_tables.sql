SET FOREIGN_KEY_CHECKS = 0;

-- Geofence-Device Associations Table
CREATE TABLE IF NOT EXISTS `geofence_devices` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geofence_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_geofence_device` (`geofence_id`, `device_id`),
  KEY `idx_geofence` (`geofence_id`),
  KEY `idx_device` (`device_id`),
  CONSTRAINT `fk_geofence_devices_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_devices_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Geofence-Group Associations Table
CREATE TABLE IF NOT EXISTS `geofence_groups` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geofence_id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_geofence_group` (`geofence_id`, `group_id`),
  KEY `idx_geofence` (`geofence_id`),
  KEY `idx_group` (`group_id`),
  CONSTRAINT `fk_geofence_groups_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `device_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

