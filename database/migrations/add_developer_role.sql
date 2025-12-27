-- Add Developer role to users.role enum
-- Developer is the top-level role with all capabilities

SET FOREIGN_KEY_CHECKS = 0;

-- Expand users.role enum to include Developer
ALTER TABLE `users` 
MODIFY COLUMN `role` enum('Guest','ReadOnly','Operator','Administrator','TenantOwner','Developer') DEFAULT 'Guest';

SET FOREIGN_KEY_CHECKS = 1;

