-- Safe update for broadcast_settings table
-- This script handles existing data without causing duplicate key errors

-- First, ensure the table exists with correct structure
CREATE TABLE IF NOT EXISTS `broadcast_settings` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `key` varchar(255) NOT NULL,
    `value` text,
    `type` varchar(255) NOT NULL DEFAULT 'string',
    `group` varchar(255) NOT NULL DEFAULT 'general',
    `label` varchar(255) NOT NULL,
    `description` text,
    `is_required` tinyint(1) NOT NULL DEFAULT '0',
    `is_sensitive` tinyint(1) NOT NULL DEFAULT '0',
    `validation_rules` json,
    `options` json,
    `sort_order` int NOT NULL DEFAULT '0',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `broadcast_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert or update settings safely using ON DUPLICATE KEY UPDATE
INSERT INTO `broadcast_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_required`, `is_sensitive`, `validation_rules`, `options`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('broadcast_driver', 'pusher', 'string', 'general', 'Broadcast Driver', 'The broadcasting driver to use (pusher, redis, log, null)', 0, 0, NULL, '{"pusher": "Pusher (Cloud/Reverb)", "redis": "Redis", "log": "Log (Development)", "null": "Disabled"}', 1, 1, NOW(), NOW()),
('pusher_service_type', 'reverb', 'string', 'general', 'Pusher Service Type', 'Choose between Pusher Cloud API or Laravel Reverb (self-hosted)', 0, 0, NULL, '{"pusher_cloud": "Pusher Cloud API (pusher.com)", "reverb": "Laravel Reverb (Self-hosted)"}', 2, 1, NOW(), NOW()),
('broadcast_enabled', 'true', 'boolean', 'general', 'Enable Broadcasting', 'Enable or disable real-time broadcasting features', 0, 0, NULL, NULL, 3, 1, NOW(), NOW()),

-- Pusher Cloud API Settings
('pusher_cloud_app_id', '', 'string', 'pusher_cloud', 'Pusher Cloud App ID', 'Your Pusher Cloud application ID from pusher.com dashboard', 0, 0, '["string"]', NULL, 1, 1, NOW(), NOW()),
('pusher_cloud_app_key', '', 'string', 'pusher_cloud', 'Pusher Cloud App Key', 'Your Pusher Cloud application key from pusher.com dashboard', 0, 0, '["string"]', NULL, 2, 1, NOW(), NOW()),
('pusher_cloud_app_secret', '', 'string', 'pusher_cloud', 'Pusher Cloud App Secret', 'Your Pusher Cloud application secret from pusher.com dashboard', 0, 1, '["string"]', NULL, 3, 1, NOW(), NOW()),
('pusher_cloud_cluster', 'us2', 'string', 'pusher_cloud', 'Pusher Cloud Cluster', 'Your Pusher Cloud cluster (e.g., us2, eu, ap1)', 0, 0, '["string"]', '{"us2": "US East (us2)", "us3": "US West (us3)", "eu": "Europe (eu)", "ap1": "Asia Pacific (ap1)", "ap2": "Asia Pacific 2 (ap2)", "ap3": "Asia Pacific 3 (ap3)", "ap4": "Asia Pacific 4 (ap4)"}', 4, 1, NOW(), NOW()),
('pusher_cloud_use_tls', 'true', 'boolean', 'pusher_cloud', 'Use TLS/SSL', 'Enable TLS/SSL for Pusher Cloud connections (recommended)', 0, 0, NULL, NULL, 5, 1, NOW(), NOW()),
-- Laravel Reverb Settings (Self-hosted)
('pusher_app_id', 'chatapp-id', 'string', 'pusher', 'Reverb App ID', 'Laravel Reverb application ID (for self-hosted)', 0, 0, NULL, NULL, 1, 1, NOW(), NOW()),
('pusher_app_key', 'chatapp-key', 'string', 'pusher', 'Reverb App Key', 'Laravel Reverb application key (for self-hosted)', 0, 0, NULL, NULL, 2, 1, NOW(), NOW()),
('pusher_app_secret', 'chatapp-secret', 'string', 'pusher', 'Reverb App Secret', 'Laravel Reverb application secret (for self-hosted)', 0, 1, NULL, NULL, 3, 1, NOW(), NOW()),
('pusher_host', '127.0.0.1', 'string', 'pusher', 'Reverb Host', 'Laravel Reverb server host (for self-hosted)', 0, 0, NULL, NULL, 4, 1, NOW(), NOW()),
('pusher_port', '8080', 'integer', 'pusher', 'Reverb Port', 'Laravel Reverb server port (for self-hosted)', 0, 0, NULL, NULL, 5, 1, NOW(), NOW()),
('pusher_scheme', 'http', 'string', 'pusher', 'Reverb Scheme', 'Protocol scheme for Reverb (http or https)', 0, 0, NULL, '{"http": "HTTP", "https": "HTTPS"}', 6, 1, NOW(), NOW()),
('reverb_app_id', 'chatapp-id', 'string', 'reverb', 'Reverb App ID', 'Laravel Reverb application ID', 0, 0, NULL, NULL, 1, 1, NOW(), NOW()),
('reverb_app_key', 'chatapp-key', 'string', 'reverb', 'Reverb App Key', 'Laravel Reverb application key', 0, 0, NULL, NULL, 2, 1, NOW(), NOW()),
('reverb_app_secret', 'chatapp-secret', 'string', 'reverb', 'Reverb App Secret', 'Laravel Reverb application secret', 0, 1, NULL, NULL, 3, 1, NOW(), NOW()),
('reverb_host', '0.0.0.0', 'string', 'reverb', 'Reverb Host', 'Laravel Reverb server host', 0, 0, NULL, NULL, 4, 1, NOW(), NOW()),
('reverb_port', '8080', 'integer', 'reverb', 'Reverb Port', 'Laravel Reverb server port', 0, 0, NULL, NULL, 5, 1, NOW(), NOW()),
('websocket_host', '127.0.0.1', 'string', 'websocket', 'WebSocket Host', 'WebSocket host for client connections', 0, 0, NULL, NULL, 1, 1, NOW(), NOW()),
('websocket_port', '6001', 'integer', 'websocket', 'WebSocket Port', 'WebSocket port for client connections', 0, 0, NULL, NULL, 2, 1, NOW(), NOW()),
('websocket_force_tls', 'false', 'boolean', 'websocket', 'Force TLS', 'Force TLS/SSL for WebSocket connections', 0, 0, NULL, NULL, 3, 1, NOW(), NOW()),
('max_connections', '1000', 'integer', 'performance', 'Max Connections', 'Maximum number of concurrent WebSocket connections', 0, 0, NULL, NULL, 1, 1, NOW(), NOW()),
('connection_timeout', '30', 'integer', 'performance', 'Connection Timeout', 'Connection timeout in seconds', 0, 0, NULL, NULL, 2, 1, NOW(), NOW()),
('ping_interval', '25', 'integer', 'performance', 'Ping Interval', 'Ping interval in seconds', 0, 0, NULL, NULL, 3, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`),
    `type` = VALUES(`type`),
    `group` = VALUES(`group`),
    `label` = VALUES(`label`),
    `description` = VALUES(`description`),
    `is_required` = VALUES(`is_required`),
    `is_sensitive` = VALUES(`is_sensitive`),
    `validation_rules` = VALUES(`validation_rules`),
    `options` = VALUES(`options`),
    `sort_order` = VALUES(`sort_order`),
    `is_active` = VALUES(`is_active`),
    `updated_at` = NOW();

-- Mark migration as completed (only if not already marked)
INSERT IGNORE INTO `migrations` (`migration`, `batch`) 
VALUES ('2024_01_20_000000_create_broadcast_settings_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- Verify the setup
SELECT 'broadcast_settings table updated successfully!' as status;
SELECT COUNT(*) as total_settings FROM broadcast_settings;
SELECT `group`, COUNT(*) as count_per_group FROM broadcast_settings GROUP BY `group`;

-- Show current settings
SELECT `key`, `value`, `group`, `label` FROM broadcast_settings ORDER BY `group`, `sort_order`;
