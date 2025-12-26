-- Trip Management System
-- Automatic trip detection and tracking

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `trips` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED DEFAULT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `start_lat` decimal(10,8) NOT NULL,
  `start_lon` decimal(11,8) NOT NULL,
  `end_lat` decimal(10,8) DEFAULT NULL,
  `end_lon` decimal(11,8) DEFAULT NULL,
  `start_address` text DEFAULT NULL,
  `end_address` text DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT 0.00,
  `duration_minutes` int(11) DEFAULT 0,
  `max_speed` decimal(5,2) DEFAULT NULL,
  `avg_speed` decimal(5,2) DEFAULT NULL,
  `idle_time_minutes` int(11) DEFAULT 0,
  `fuel_consumed` decimal(8,2) DEFAULT NULL,
  `start_odometer` decimal(10,2) DEFAULT NULL,
  `end_odometer` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_device` (`tenant_id`, `device_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_device_start` (`device_id`, `start_time` DESC),
  CONSTRAINT `fk_trips_tenant_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trips_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trips_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip waypoints table (stores route points for playback)
CREATE TABLE IF NOT EXISTS `trip_waypoints` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `telemetry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sequence` int(11) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lon` decimal(11,8) NOT NULL,
  `timestamp` timestamp NOT NULL,
  `speed` decimal(5,2) DEFAULT NULL,
  `heading` int(11) DEFAULT NULL,
  `altitude` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trip_sequence` (`trip_id`, `sequence`),
  KEY `idx_trip_timestamp` (`trip_id`, `timestamp`),
  CONSTRAINT `fk_trip_waypoints_trip_id` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_waypoints_telemetry_id` FOREIGN KEY (`telemetry_id`) REFERENCES `telemetry` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

