SET FOREIGN_KEY_CHECKS = 0;

-- Add actions field to geofences table if it doesn't exist
-- Note: The schema already has a 'rules' field, but we'll add an 'actions' field for linkable actions
-- Note: MariaDB doesn't support IF NOT EXISTS for ADD COLUMN, so the migration execution will handle errors gracefully
ALTER TABLE `geofences` 
ADD COLUMN `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL AFTER `rules`;

SET FOREIGN_KEY_CHECKS = 1;

