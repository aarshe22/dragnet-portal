-- Geofence Events Tracking
-- Tracks entry/exit events and dwell time for analytics

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `geofence_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `geofence_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `event_type` enum('entry','exit') NOT NULL,
  `timestamp` timestamp NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lon` decimal(11,8) NOT NULL,
  `speed` decimal(5,2) DEFAULT NULL,
  `heading` int(11) DEFAULT NULL,
  `telemetry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_geofence` (`tenant_id`, `geofence_id`),
  KEY `idx_device_geofence` (`device_id`, `geofence_id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_telemetry` (`telemetry_id`),
  CONSTRAINT `fk_geofence_events_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_events_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_events_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_events_telemetry_id` FOREIGN KEY (`telemetry_id`) REFERENCES `telemetry` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Device geofence state tracking (tracks current state for each device-geofence pair)
CREATE TABLE IF NOT EXISTS `device_geofence_state` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `geofence_id` int(10) UNSIGNED NOT NULL,
  `is_inside` tinyint(1) DEFAULT 0,
  `entry_time` timestamp NULL DEFAULT NULL,
  `last_seen_inside` timestamp NULL DEFAULT NULL,
  `last_telemetry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_device_geofence` (`device_id`, `geofence_id`),
  KEY `idx_tenant_device` (`tenant_id`, `device_id`),
  KEY `idx_geofence` (`geofence_id`),
  KEY `idx_is_inside` (`is_inside`),
  CONSTRAINT `fk_device_geofence_state_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_device_geofence_state_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_device_geofence_state_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

