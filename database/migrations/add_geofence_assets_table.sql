SET FOREIGN_KEY_CHECKS = 0;

-- Geofence-Asset Associations Table
CREATE TABLE IF NOT EXISTS `geofence_assets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geofence_id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_geofence_asset` (`geofence_id`, `asset_id`),
  KEY `idx_geofence` (`geofence_id`),
  KEY `idx_asset` (`asset_id`),
  CONSTRAINT `fk_geofence_assets_geofence_id` FOREIGN KEY (`geofence_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_geofence_assets_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

