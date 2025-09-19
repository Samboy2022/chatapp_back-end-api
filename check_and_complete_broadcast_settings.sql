-- Check current state and complete broadcast settings setup
-- This script is safe to run multiple times

-- First, let's see what we currently have
SELECT 'Current broadcast_settings table status:' as info;
SELECT COUNT(*) as total_records FROM broadcast_settings;
SELECT DISTINCT `group` as existing_groups FROM broadcast_settings;

-- Check if we have all required groups
SELECT 
    CASE 
        WHEN (SELECT COUNT(DISTINCT `group`) FROM broadcast_settings) >= 5 THEN 'All groups present'
        ELSE 'Missing some groups'
    END as group_status;

-- Insert missing settings only (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO `broadcast_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_required`, `is_sensitive`, `validation_rules`, `options`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
-- General Settings
('broadcast_enabled', 'true', 'boolean', 'general', 'Enable Broadcasting', 'Enable or disable real-time broadcasting features', 1, 0, NULL, NULL, 2, 1, NOW(), NOW()),

-- Pusher Settings (only if not exists)
('pusher_app_id', 'chatapp-id', 'string', 'pusher', 'Pusher App ID', 'Your Pusher application ID', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('pusher_app_key', 'chatapp-key', 'string', 'pusher', 'Pusher App Key', 'Your Pusher application key', 1, 0, '["required", "string"]', NULL, 2, 1, NOW(), NOW()),
('pusher_app_secret', 'chatapp-secret', 'string', 'pusher', 'Pusher App Secret', 'Your Pusher application secret', 1, 1, '["required", "string"]', NULL, 3, 1, NOW(), NOW()),
('pusher_host', '127.0.0.1', 'string', 'pusher', 'Pusher Host', 'Pusher/Reverb server host', 1, 0, '["required", "string"]', NULL, 4, 1, NOW(), NOW()),
('pusher_port', '8080', 'integer', 'pusher', 'Pusher Port', 'Pusher/Reverb server port', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 5, 1, NOW(), NOW()),
('pusher_scheme', 'http', 'string', 'pusher', 'Pusher Scheme', 'Protocol scheme (http or https)', 1, 0, '["required", "in:http,https"]', '{"http": "HTTP", "https": "HTTPS"}', 6, 1, NOW(), NOW()),
('pusher_cluster', '', 'string', 'pusher', 'Pusher Cluster', 'Pusher cluster (leave empty for Reverb)', 0, 0, NULL, NULL, 7, 1, NOW(), NOW()),

-- Reverb Settings
('reverb_app_id', 'chatapp-id', 'string', 'reverb', 'Reverb App ID', 'Laravel Reverb application ID', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('reverb_app_key', 'chatapp-key', 'string', 'reverb', 'Reverb App Key', 'Laravel Reverb application key', 1, 0, '["required", "string"]', NULL, 2, 1, NOW(), NOW()),
('reverb_app_secret', 'chatapp-secret', 'string', 'reverb', 'Reverb App Secret', 'Laravel Reverb application secret', 1, 1, '["required", "string"]', NULL, 3, 1, NOW(), NOW()),
('reverb_host', '0.0.0.0', 'string', 'reverb', 'Reverb Host', 'Laravel Reverb server host', 1, 0, '["required", "string"]', NULL, 4, 1, NOW(), NOW()),
('reverb_port', '8080', 'integer', 'reverb', 'Reverb Port', 'Laravel Reverb server port', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 5, 1, NOW(), NOW()),

-- WebSocket Settings
('websocket_host', '127.0.0.1', 'string', 'websocket', 'WebSocket Host', 'WebSocket host for client connections', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('websocket_port', '6001', 'integer', 'websocket', 'WebSocket Port', 'WebSocket port for client connections', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 2, 1, NOW(), NOW()),
('websocket_force_tls', 'false', 'boolean', 'websocket', 'Force TLS', 'Force TLS/SSL for WebSocket connections', 0, 0, NULL, NULL, 3, 1, NOW(), NOW()),

-- Performance Settings
('max_connections', '1000', 'integer', 'performance', 'Max Connections', 'Maximum number of concurrent WebSocket connections', 1, 0, '["required", "integer", "min:1"]', NULL, 1, 1, NOW(), NOW()),
('connection_timeout', '30', 'integer', 'performance', 'Connection Timeout', 'Connection timeout in seconds', 1, 0, '["required", "integer", "min:5", "max:300"]', NULL, 2, 1, NOW(), NOW()),
('ping_interval', '25', 'integer', 'performance', 'Ping Interval', 'Ping interval in seconds', 1, 0, '["required", "integer", "min:5", "max:60"]', NULL, 3, 1, NOW(), NOW());

-- Mark migration as completed (only if not already marked)
INSERT IGNORE INTO `migrations` (`migration`, `batch`) 
VALUES ('2024_01_20_000000_create_broadcast_settings_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- Final verification
SELECT 'Setup completed successfully!' as status;
SELECT COUNT(*) as total_settings FROM broadcast_settings;
SELECT `group`, COUNT(*) as settings_per_group FROM broadcast_settings WHERE is_active = 1 GROUP BY `group` ORDER BY `group`;

-- Show what we have now
SELECT 'Current broadcast settings:' as info;
SELECT `key`, `value`, `group` FROM broadcast_settings WHERE is_active = 1 ORDER BY `group`, `sort_order`, `key`;
