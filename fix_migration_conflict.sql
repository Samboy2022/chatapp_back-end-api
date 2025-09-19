-- Fix migration conflict by marking sessions migration as completed
-- Run this SQL in your MySQL database first

-- Mark the sessions migration as completed (since the table already exists)
INSERT IGNORE INTO `migrations` (`migration`, `batch`) 
VALUES ('2025_06_14_193038_create_sessions_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- Verify migrations table
SELECT * FROM migrations WHERE migration LIKE '%sessions%';

-- Now you can run: php artisan migrate
-- This will skip the sessions table and run other pending migrations including broadcast_settings
