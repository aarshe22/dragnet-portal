-- Migration: Remove device_id from assets table
-- This migration supports the new many-to-one relationship where devices link to assets via asset_id
-- Any existing device_id values in assets will be migrated to device.asset_id before removal

SET FOREIGN_KEY_CHECKS = 0;

-- Step 1: Migrate existing device_id relationships to device.asset_id
-- This ensures no data is lost when we remove the column
UPDATE devices d
INNER JOIN assets a ON a.device_id = d.id
SET d.asset_id = a.id
WHERE d.asset_id IS NULL OR d.asset_id = 0;

-- Step 2: Remove the device_id column and its index from assets table
ALTER TABLE `assets` 
DROP INDEX IF EXISTS `idx_device`,
DROP COLUMN IF EXISTS `device_id`;

SET FOREIGN_KEY_CHECKS = 1;

