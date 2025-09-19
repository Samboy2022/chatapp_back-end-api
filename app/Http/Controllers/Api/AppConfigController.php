<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BroadcastSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppConfigController extends Controller
{
    /**
     * Get app configuration for mobile app
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig()
    {
        try {
            // Cache the config for 5 minutes to reduce database load
            $config = Cache::remember('mobile_app_config', 300, function () {
                return BroadcastSetting::getAppConfig();
            });

            // Add server timestamp for cache validation
            $config['server_timestamp'] = now()->timestamp;
            $config['config_version'] = '2.0.0';

            // Add enhanced broadcast configuration
            $config['broadcast_service_type'] = BroadcastSetting::getValue('pusher_service_type', 'reverb');
            $config['real_time_features'] = [
                'chat_messaging' => $config['broadcast_enabled'],
                'typing_indicators' => $config['broadcast_enabled'],
                'user_presence' => $config['broadcast_enabled'],
                'message_delivery_status' => $config['broadcast_enabled'],
                'real_time_notifications' => $config['broadcast_enabled']
            ];

            // Add mobile app specific settings
            $config['mobile_settings'] = [
                'auto_reconnect' => true,
                'connection_timeout' => 30,
                'max_reconnect_attempts' => 5,
                'heartbeat_interval' => 25
            ];

            Log::info('App config requested', [
                'broadcast_enabled' => $config['broadcast_enabled'],
                'broadcast_type' => $config['broadcast_type'],
                'service_type' => $config['broadcast_service_type'],
                'timestamp' => $config['server_timestamp']
            ]);

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configuration retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get app config: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback configuration
            return response()->json([
                'success' => false,
                'data' => $this->getFallbackConfig(),
                'message' => 'Using fallback configuration',
                'error' => 'Configuration service temporarily unavailable'
            ], 200); // Still return 200 so app can function with fallback
        }
    }

    /**
     * Get minimal fallback configuration
     */
    private function getFallbackConfig()
    {
        return [
            'broadcast_enabled' => false,
            'broadcast_type' => 'reverb',
            'app_name' => config('app.name', 'FarmersNetwork'),
            'app_logo' => asset('images/logo.png'),
            'walkthrough_message' => 'Welcome to FarmersNetwork!',
            'api_base_url' => url('/api'),
            'websocket_auth_endpoint' => url('/broadcasting/auth'),
            'server_timestamp' => now()->timestamp,
            'config_version' => '1.0.0',
            'fallback' => true
        ];
    }

    /**
     * Validate app configuration (for admin testing)
     */
    public function validateConfig()
    {
        try {
            $config = BroadcastSetting::getAppConfig();
            $validation = $this->performConfigValidation($config);

            return response()->json([
                'success' => true,
                'config' => $config,
                'validation' => $validation,
                'message' => 'Configuration validation completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Config validation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Configuration validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform configuration validation
     */
    private function performConfigValidation($config)
    {
        $validation = [
            'overall_status' => 'valid',
            'checks' => [],
            'warnings' => [],
            'errors' => []
        ];

        // Check broadcast configuration
        if ($config['broadcast_enabled']) {
            if ($config['broadcast_type'] === 'pusher_cloud') {
                // Validate Pusher Cloud settings
                if (empty($config['pusher_key'])) {
                    $validation['errors'][] = 'Pusher Cloud key is missing';
                }
                if (empty($config['pusher_cluster'])) {
                    $validation['warnings'][] = 'Pusher cluster not specified, using default';
                }
                $validation['checks']['pusher_cloud'] = 'configured';
            } else {
                // Validate Reverb settings
                if (empty($config['pusher_key'])) {
                    $validation['errors'][] = 'Reverb app key is missing';
                }
                if (empty($config['reverb_host'])) {
                    $validation['errors'][] = 'Reverb host is missing';
                }
                if (empty($config['reverb_port'])) {
                    $validation['errors'][] = 'Reverb port is missing';
                }
                $validation['checks']['reverb'] = 'configured';
            }
        } else {
            $validation['checks']['broadcast'] = 'disabled';
            $validation['warnings'][] = 'Real-time broadcasting is disabled';
        }

        // Check app settings
        if (empty($config['app_name'])) {
            $validation['warnings'][] = 'App name is not configured';
        }

        // Set overall status
        if (!empty($validation['errors'])) {
            $validation['overall_status'] = 'invalid';
        } elseif (!empty($validation['warnings'])) {
            $validation['overall_status'] = 'warning';
        }

        return $validation;
    }

    /**
     * Clear configuration cache
     */
    public function clearCache()
    {
        try {
            Cache::forget('mobile_app_config');
            Cache::forget('broadcast_config');
            Cache::forget('broadcast_settings');

            Log::info('App configuration cache cleared');

            return response()->json([
                'success' => true,
                'message' => 'Configuration cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear config cache: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear configuration cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configuration history/changelog
     */
    public function getConfigHistory()
    {
        try {
            // This could be expanded to track configuration changes
            $history = [
                [
                    'version' => '1.0.0',
                    'date' => now()->toDateString(),
                    'changes' => [
                        'Initial configuration system',
                        'Support for Pusher Cloud and Laravel Reverb',
                        'Dynamic broadcast type switching'
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'history' => $history,
                'current_version' => '1.0.0'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get configuration history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
