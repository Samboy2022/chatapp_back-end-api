-- Create broadcast_settings table manually
-- Run this SQL in your MySQL database

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

-- Insert default settings
INSERT INTO `broadcast_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_required`, `is_sensitive`, `validation_rules`, `options`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('broadcast_driver', 'pusher', 'string', 'general', 'Broadcast Driver', 'The broadcasting driver to use (pusher, redis, log, null)', 1, 0, '["required", "in:pusher,redis,log,null"]', '{"pusher": "Pusher/Reverb", "redis": "Redis", "log": "Log (Development)", "null": "Disabled"}', 1, 1, NOW(), NOW()),
('broadcast_enabled', 'true', 'boolean', 'general', 'Enable Broadcasting', 'Enable or disable real-time broadcasting features', 1, 0, NULL, NULL, 2, 1, NOW(), NOW()),
('pusher_app_id', 'chatapp-id', 'string', 'pusher', 'Pusher App ID', 'Your Pusher application ID', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('pusher_app_key', 'chatapp-key', 'string', 'pusher', 'Pusher App Key', 'Your Pusher application key', 1, 0, '["required", "string"]', NULL, 2, 1, NOW(), NOW()),
('pusher_app_secret', 'chatapp-secret', 'string', 'pusher', 'Pusher App Secret', 'Your Pusher application secret', 1, 1, '["required", "string"]', NULL, 3, 1, NOW(), NOW()),
('pusher_host', '127.0.0.1', 'string', 'pusher', 'Pusher Host', 'Pusher/Reverb server host', 1, 0, '["required", "string"]', NULL, 4, 1, NOW(), NOW()),
('pusher_port', '8080', 'integer', 'pusher', 'Pusher Port', 'Pusher/Reverb server port', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 5, 1, NOW(), NOW()),
('pusher_scheme', 'http', 'string', 'pusher', 'Pusher Scheme', 'Protocol scheme (http or https)', 1, 0, '["required", "in:http,https"]', '{"http": "HTTP", "https": "HTTPS"}', 6, 1, NOW(), NOW()),
('pusher_cluster', '', 'string', 'pusher', 'Pusher Cluster', 'Pusher cluster (leave empty for Reverb)', 0, 0, NULL, NULL, 7, 1, NOW(), NOW()),
('reverb_app_id', 'chatapp-id', 'string', 'reverb', 'Reverb App ID', 'Laravel Reverb application ID', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('reverb_app_key', 'chatapp-key', 'string', 'reverb', 'Reverb App Key', 'Laravel Reverb application key', 1, 0, '["required", "string"]', NULL, 2, 1, NOW(), NOW()),
('reverb_app_secret', 'chatapp-secret', 'string', 'reverb', 'Reverb App Secret', 'Laravel Reverb application secret', 1, 1, '["required", "string"]', NULL, 3, 1, NOW(), NOW()),
('reverb_host', '0.0.0.0', 'string', 'reverb', 'Reverb Host', 'Laravel Reverb server host', 1, 0, '["required", "string"]', NULL, 4, 1, NOW(), NOW()),
('reverb_port', '8080', 'integer', 'reverb', 'Reverb Port', 'Laravel Reverb server port', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 5, 1, NOW(), NOW()),
('websocket_host', '127.0.0.1', 'string', 'websocket', 'WebSocket Host', 'WebSocket host for client connections', 1, 0, '["required", "string"]', NULL, 1, 1, NOW(), NOW()),
('websocket_port', '6001', 'integer', 'websocket', 'WebSocket Port', 'WebSocket port for client connections', 1, 0, '["required", "integer", "min:1", "max:65535"]', NULL, 2, 1, NOW(), NOW()),
('websocket_force_tls', 'false', 'boolean', 'websocket', 'Force TLS', 'Force TLS/SSL for WebSocket connections', 0, 0, NULL, NULL, 3, 1, NOW(), NOW()),
('max_connections', '1000', 'integer', 'performance', 'Max Connections', 'Maximum number of concurrent WebSocket connections', 1, 0, '["required", "integer", "min:1"]', NULL, 1, 1, NOW(), NOW()),
('connection_timeout', '30', 'integer', 'performance', 'Connection Timeout', 'Connection timeout in seconds', 1, 0, '["required", "integer", "min:5", "max:300"]', NULL, 2, 1, NOW(), NOW()),
('ping_interval', '25', 'integer', 'performance', 'Ping Interval', 'Ping interval in seconds', 1, 0, '["required", "integer", "min:5", "max:60"]', NULL, 3, 1, NOW(), NOW());

-- Mark migration as completed (adjust batch number as needed)
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2024_01_20_000000_create_broadcast_settings_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- Verify the table was created
SELECT 'broadcast_settings table created successfully!' as status;
SELECT COUNT(*) as settings_count FROM broadcast_settings;
