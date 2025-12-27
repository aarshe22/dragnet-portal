-- Update the first administrator user to Developer role
-- This migration should be run after add_developer_role.sql

SET FOREIGN_KEY_CHECKS = 0;

-- Update the first user with Administrator role to Developer
-- You may need to adjust this query based on your specific user
UPDATE `users` 
SET `role` = 'Developer' 
WHERE `role` = 'Administrator' 
ORDER BY `id` ASC 
LIMIT 1;

SET FOREIGN_KEY_CHECKS = 1;

