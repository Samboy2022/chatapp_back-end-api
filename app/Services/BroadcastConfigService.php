<?php

namespace App\Services;

use App\Models\BroadcastSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class BroadcastConfigService
{
    /**
     * Apply broadcast settings to Laravel configuration
     */
    public static function applySettings()
    {
        try {
            // Check if table exists first
            if (!\Schema::hasTable('broadcast_settings')) {
                Log::info('Broadcast settings table not found, using environment defaults');
                return;
            }

            $settings = BroadcastSetting::getBroadcastConfig();

            // Apply broadcasting driver
            if (isset($settings['broadcast_driver'])) {
                Config::set('broadcasting.default', $settings['broadcast_driver']);
            }

            // Apply Pusher configuration
            self::applyPusherConfig($settings);

            // Apply Reverb configuration
            self::applyReverbConfig($settings);

            Log::info('Broadcast configuration applied successfully');

        } catch (\Exception $e) {
            Log::error('Failed to apply broadcast configuration: ' . $e->getMessage());
        }
    }

    /**
     * Apply Pusher configuration
     */
    private static function applyPusherConfig($settings)
    {
        $pusherConfig = [
            'driver' => 'pusher',
            'key' => $settings['pusher_app_key'] ?? env('PUSHER_APP_KEY'),
            'secret' => $settings['pusher_app_secret'] ?? env('PUSHER_APP_SECRET'),
            'app_id' => $settings['pusher_app_id'] ?? env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => $settings['pusher_cluster'] ?? env('PUSHER_CLUSTER'),
                'host' => $settings['pusher_host'] ?? env('PUSHER_HOST'),
                'port' => $settings['pusher_port'] ?? env('PUSHER_PORT'),
                'scheme' => $settings['pusher_scheme'] ?? env('PUSHER_SCHEME', 'http'),
                'encrypted' => ($settings['pusher_scheme'] ?? 'http') === 'https',
                'useTLS' => ($settings['pusher_scheme'] ?? 'http') === 'https',
            ],
        ];

        Config::set('broadcasting.connections.pusher', $pusherConfig);
    }

    /**
     * Apply Reverb configuration
     */
    private static function applyReverbConfig($settings)
    {
        $reverbConfig = [
            'driver' => 'reverb',
            'key' => $settings['reverb_app_key'] ?? env('REVERB_APP_KEY'),
            'secret' => $settings['reverb_app_secret'] ?? env('REVERB_APP_SECRET'),
            'app_id' => $settings['reverb_app_id'] ?? env('REVERB_APP_ID'),
            'options' => [
                'host' => $settings['reverb_host'] ?? env('REVERB_HOST'),
                'port' => $settings['reverb_port'] ?? env('REVERB_PORT'),
                'scheme' => 'http',
                'useTLS' => false,
            ],
        ];

        Config::set('broadcasting.connections.reverb', $reverbConfig);
    }

    /**
     * Get client configuration for frontend
     */
    public static function getClientConfig()
    {
        $settings = BroadcastSetting::getBroadcastConfig();
        $driver = $settings['broadcast_driver'] ?? 'pusher';

        $config = [
            'enabled' => $settings['broadcast_enabled'] ?? true,
            'driver' => $driver,
        ];

        if ($driver === 'pusher') {
            $config['pusher'] = [
                'key' => $settings['pusher_app_key'] ?? env('PUSHER_APP_KEY'),
                'cluster' => $settings['pusher_cluster'] ?? env('PUSHER_CLUSTER'),
                'wsHost' => $settings['websocket_host'] ?? $settings['pusher_host'] ?? env('PUSHER_HOST'),
                'wsPort' => $settings['websocket_port'] ?? 6001,
                'wssPort' => $settings['websocket_port'] ?? 6001,
                'forceTLS' => $settings['websocket_force_tls'] ?? false,
                'encrypted' => $settings['websocket_force_tls'] ?? false,
                'disableStats' => true,
                'enabledTransports' => ['ws', 'wss'],
            ];
        }

        return $config;
    }

    /**
     * Validate broadcast configuration
     */
    public static function validateConfig()
    {
        $settings = BroadcastSetting::getBroadcastConfig();
        $errors = [];

        // Check if broadcasting is enabled
        if (!($settings['broadcast_enabled'] ?? true)) {
            return [
                'valid' => false,
                'message' => 'Broadcasting is disabled',
                'errors' => ['Broadcasting is currently disabled in settings']
            ];
        }

        $driver = $settings['broadcast_driver'] ?? 'pusher';

        switch ($driver) {
            case 'pusher':
                $errors = array_merge($errors, self::validatePusherConfig($settings));
                break;
            case 'redis':
                $errors = array_merge($errors, self::validateRedisConfig($settings));
                break;
        }

        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Configuration is valid' : 'Configuration has errors',
            'errors' => $errors
        ];
    }

    /**
     * Validate Pusher configuration
     */
    private static function validatePusherConfig($settings)
    {
        $errors = [];
        $required = ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_host', 'pusher_port'];

        foreach ($required as $key) {
            if (empty($settings[$key])) {
                $errors[] = "Missing required Pusher setting: {$key}";
            }
        }

        // Validate port
        $port = $settings['pusher_port'] ?? null;
        if ($port && (!is_numeric($port) || $port < 1 || $port > 65535)) {
            $errors[] = 'Pusher port must be a valid port number (1-65535)';
        }

        return $errors;
    }

    /**
     * Validate Redis configuration
     */
    private static function validateRedisConfig($settings)
    {
        $errors = [];

        try {
            $redis = app('redis');
            $redis->ping();
        } catch (\Exception $e) {
            $errors[] = 'Redis connection failed: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Test broadcast connection
     */
    public static function testConnection()
    {
        $settings = BroadcastSetting::getBroadcastConfig();
        $driver = $settings['broadcast_driver'] ?? 'pusher';

        switch ($driver) {
            case 'pusher':
                return self::testPusherConnection($settings);
            case 'redis':
                return self::testRedisConnection();
            default:
                return [
                    'success' => false,
                    'message' => "Connection testing not supported for driver: {$driver}"
                ];
        }
    }

    /**
     * Test Pusher connection
     */
    private static function testPusherConnection($settings)
    {
        try {
            $host = $settings['pusher_host'] ?? 'localhost';
            $port = $settings['pusher_port'] ?? 8080;
            $scheme = $settings['pusher_scheme'] ?? 'http';
            $appKey = $settings['pusher_app_key'] ?? '';

            $url = "{$scheme}://{$host}:{$port}/app/{$appKey}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                return [
                    'success' => true,
                    'message' => 'Pusher connection successful',
                    'url' => $url
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to Pusher server',
                    'url' => $url
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Pusher connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Redis connection
     */
    private static function testRedisConnection()
    {
        try {
            $redis = app('redis');
            $result = $redis->ping();
            
            return [
                'success' => true,
                'message' => 'Redis connection successful',
                'response' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get performance metrics
     */
    public static function getPerformanceMetrics()
    {
        $settings = BroadcastSetting::getBroadcastConfig();
        
        return [
            'max_connections' => $settings['max_connections'] ?? 1000,
            'connection_timeout' => $settings['connection_timeout'] ?? 30,
            'ping_interval' => $settings['ping_interval'] ?? 25,
            'current_connections' => self::getCurrentConnectionCount(),
            'memory_usage' => memory_get_usage(true),
            'uptime' => self::getServerUptime(),
        ];
    }

    /**
     * Get current connection count (placeholder)
     */
    private static function getCurrentConnectionCount()
    {
        // This would need to be implemented based on your WebSocket server
        // For now, return a placeholder
        return 0;
    }

    /**
     * Get server uptime (placeholder)
     */
    private static function getServerUptime()
    {
        // This would need to be implemented based on your server setup
        // For now, return a placeholder
        return '0 days, 0 hours, 0 minutes';
    }

    /**
     * Clear all broadcast-related caches
     */
    public static function clearCache()
    {
        Cache::forget('broadcast_settings');
        Cache::forget('broadcast_config');
        Cache::tags(['broadcast'])->flush();
        
        Log::info('Broadcast cache cleared');
    }
}
