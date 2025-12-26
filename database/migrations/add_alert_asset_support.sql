SET FOREIGN_KEY_CHECKS = 0;

-- Add asset_id to alerts table (nullable, since alerts can be for devices or assets)
ALTER TABLE `alerts` 
ADD COLUMN `asset_id` int(10) UNSIGNED DEFAULT NULL AFTER `device_id`,
ADD KEY `idx_asset` (`asset_id`),
ADD CONSTRAINT `fk_alerts_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

-- Make device_id nullable (alerts can be asset-only)
ALTER TABLE `alerts` 
MODIFY COLUMN `device_id` int(10) UNSIGNED DEFAULT NULL;

-- Add check constraint: either device_id or asset_id must be set
-- Note: MySQL doesn't support CHECK constraints in older versions, so we'll enforce this in application logic

SET FOREIGN_KEY_CHECKS = 1;

