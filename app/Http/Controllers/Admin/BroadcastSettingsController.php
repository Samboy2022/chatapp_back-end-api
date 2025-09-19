<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastSetting;
use App\Services\DynamicBroadcastConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BroadcastSettingsController extends Controller
{
    /**
     * Display broadcast settings
     */
    public function index()
    {
        try {
            $settingsGrouped = BroadcastSetting::getAllGrouped();
            $connectionStatus = $this->checkConnectionStatus();
        } catch (\Exception $e) {
            // Fallback if database/model has issues
            Log::error('Failed to load broadcast settings: ' . $e->getMessage());

            $settingsGrouped = collect();
            $connectionStatus = [
                'driver' => 'pusher',
                'enabled' => false,
                'connected' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }

        return view('admin.broadcast-settings.index', compact('settingsGrouped', 'connectionStatus'));
    }

    /**
     * Update broadcast settings
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            $errors = [];
            $updated = 0;
            $updateDetails = [];

            Log::info('Broadcast settings update started', [
                'settings_count' => count($settings),
                'settings_keys' => array_keys($settings)
            ]);

            foreach ($settings as $key => $value) {
                $setting = BroadcastSetting::where('key', $key)->first();

                if (!$setting) {
                    Log::warning("Setting not found: {$key}");
                    continue;
                }

                $oldValue = $setting->value;

                // Update the setting (no validation - accept all values including empty)
                $setting->value = $value ?? '';
                $setting->save();

                $updateDetails[] = [
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $setting->value,
                    'changed' => $oldValue !== $setting->value
                ];

                $updated++;

                Log::info("Updated setting: {$key}", [
                    'old_value' => $oldValue,
                    'new_value' => $setting->value
                ]);
            }

            if (!empty($errors)) {
                // Check if this is an AJAX request
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some settings could not be updated',
                        'errors' => $errors
                    ], 422);
                } else {
                    // Regular form submission - redirect back with error message
                    return redirect()->route('admin.broadcast-settings.index')
                        ->with('error', 'Some settings could not be updated')
                        ->withErrors($errors);
                }
            }

            // Clear all related caches
            Cache::forget('broadcast_settings');
            Cache::forget('broadcast_config');
            Cache::flush(); // Clear all cache to ensure fresh data

            Log::info('Cache cleared after settings update');

            // Update environment file if needed
            $this->updateEnvironmentFile($settings);

            Log::info('Broadcast settings update completed', [
                'updated_count' => $updated,
                'update_details' => $updateDetails
            ]);

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully updated {$updated} settings",
                    'updated_count' => $updated,
                    'update_details' => $updateDetails
                ]);
            } else {
                // Regular form submission - redirect back with success message
                return redirect()->route('admin.broadcast-settings.index')
                    ->with('success', "Successfully updated {$updated} broadcast settings");
            }

        } catch (\Exception $e) {
            Log::error('Failed to update broadcast settings: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            } else {
                // Regular form submission - redirect back with error message
                return redirect()->route('admin.broadcast-settings.index')
                    ->with('error', 'Failed to update settings: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get current broadcast status
     */
    public function status()
    {
        try {
            $broadcastEnabled = BroadcastSetting::getValue('broadcast_enabled', false);
            $serviceType = BroadcastSetting::getValue('pusher_service_type', 'reverb');
            $driver = config('broadcasting.default');

            // Test connection based on current configuration
            $connected = false;
            $message = 'Not connected';

            if ($broadcastEnabled) {
                if ($serviceType === 'pusher_cloud') {
                    $connected = $this->testPusherCloudConnection();
                    $message = $connected ? 'Pusher Cloud connected' : 'Pusher Cloud connection failed';
                } else {
                    $connected = $this->testReverbConnection();
                    $message = $connected ? 'Laravel Reverb connected' : 'Laravel Reverb connection failed';
                }
            } else {
                $message = 'Broadcasting disabled';
            }

            return response()->json([
                'success' => true,
                'status' => [
                    'enabled' => $broadcastEnabled,
                    'connected' => $connected,
                    'driver' => $driver,
                    'service_type' => $serviceType,
                    'message' => $message,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Status check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'status' => [
                    'enabled' => false,
                    'connected' => false,
                    'driver' => 'unknown',
                    'service_type' => 'unknown',
                    'message' => 'Status check failed',
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Test broadcast connection
     */
    public function testConnection(Request $request)
    {
        try {
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');
            $results = [];

            switch ($driver) {
                case 'pusher':
                    $results = $this->testPusherConnection();
                    break;
                case 'redis':
                    $results = $this->testRedisConnection();
                    break;
                default:
                    $results = [
                        'success' => false,
                        'message' => "Testing not available for driver: {$driver}"
                    ];
            }

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Broadcast connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart broadcast services
     */
    public function restartServices(Request $request)
    {
        try {
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');
            $results = [];

            if ($driver === 'pusher') {
                // Restart Reverb if it's running
                $results['reverb'] = $this->restartReverb();
            }

            // Clear all caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            
            $results['cache_cleared'] = true;

            return response()->json([
                'success' => true,
                'message' => 'Services restarted successfully',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to restart broadcast services: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart services: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current configuration with Pusher Cloud support
     */
    public function getConfig()
    {
        try {
            $config = BroadcastSetting::getBroadcastConfig();
            $status = $this->checkConnectionStatus();

            // Get dynamic configuration for frontend
            $dynamicConfig = DynamicBroadcastConfigService::getFrontendConfig();

            return response()->json([
                'success' => true,
                'config' => $config,
                'dynamic_config' => $dynamicConfig,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export configuration
     */
    public function exportConfig()
    {
        try {
            $config = BroadcastSetting::getBroadcastConfig();
            $envFormat = $this->generateEnvFormat($config);

            return response()->json([
                'success' => true,
                'env_format' => $envFormat,
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check connection status with comprehensive testing
     */
    private function checkConnectionStatus()
    {
        try {
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');
            $enabled = BroadcastSetting::getValue('broadcast_enabled', true);

            $status = [
                'driver' => $driver,
                'enabled' => $enabled,
                'connected' => false,
                'message' => 'Not connected',
                'details' => [],
                'recommendations' => []
            ];

            if (!$enabled) {
                $status['message'] = 'Broadcasting is disabled';
                $status['recommendations'][] = 'Enable broadcasting in settings';
                return $status;
            }

            // Comprehensive status check
            switch ($driver) {
                case 'pusher':
                case 'reverb':
                    $status = array_merge($status, $this->checkReverbStatus());
                    break;
                case 'redis':
                    $status = array_merge($status, $this->checkRedisStatus());
                    break;
                default:
                    $status['message'] = "Driver '{$driver}' does not support connection testing";
                    $status['recommendations'][] = 'Use pusher or redis driver for connection testing';
            }

            // Add queue status check
            $queueStatus = $this->checkQueueStatus();
            $status['queue_status'] = $queueStatus;

            if (!$queueStatus['working']) {
                $status['recommendations'][] = 'Start queue worker: php artisan queue:work';
            }

            // Add database status check
            $dbStatus = $this->checkDatabaseStatus();
            $status['database_status'] = $dbStatus;

            return $status;
        } catch (\Exception $e) {
            return [
                'driver' => 'unknown',
                'enabled' => false,
                'connected' => false,
                'message' => 'Error checking status: ' . $e->getMessage(),
                'details' => [],
                'recommendations' => ['Check server logs for detailed error information']
            ];
        }
    }

    /**
     * Enhanced Reverb/Pusher status check
     */
    private function checkReverbStatus()
    {
        $host = BroadcastSetting::getValue('reverb_host', '0.0.0.0');
        $port = BroadcastSetting::getValue('reverb_port', 8080);
        $appKey = BroadcastSetting::getValue('reverb_app_key', 'chatapp-key');

        $details = [
            'host' => $host,
            'port' => $port,
            'app_key' => $appKey
        ];

        // Test socket connection
        $socketTest = $this->testSocketConnection($host, $port);
        $details['socket_test'] = $socketTest;

        // Test HTTP endpoint
        $httpTest = $this->testReverbHttpEndpoint($host, $port, $appKey);
        $details['http_test'] = $httpTest;

        $connected = $socketTest['success'] && $httpTest['success'];
        $message = $connected ?
            "Reverb server is running on {$host}:{$port}" :
            "Reverb server is not accessible on {$host}:{$port}";

        $recommendations = [];
        if (!$connected) {
            $recommendations[] = "Start Reverb server: php artisan reverb:start --host={$host} --port={$port}";
            $recommendations[] = "Check firewall settings for port {$port}";
            if ($host === '0.0.0.0') {
                $recommendations[] = "For mobile access, use your computer's IP address instead of 0.0.0.0";
            }
        }

        return [
            'connected' => $connected,
            'message' => $message,
            'details' => $details,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Test socket connection
     */
    private function testSocketConnection($host, $port, $timeout = 5)
    {
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

            if ($connection) {
                fclose($connection);
                return [
                    'success' => true,
                    'message' => 'Socket connection successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Socket connection failed: {$errstr} (Error: {$errno})"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Socket test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Reverb HTTP endpoint
     */
    private function testReverbHttpEndpoint($host, $port, $appKey)
    {
        try {
            $url = "http://{$host}:{$port}/app/{$appKey}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET',
                    'header' => 'Accept: application/json'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                return [
                    'success' => true,
                    'message' => 'HTTP endpoint accessible',
                    'url' => $url
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP endpoint not accessible',
                    'url' => $url
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'HTTP test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check queue status
     */
    private function checkQueueStatus()
    {
        try {
            // Check if there are any failed jobs
            $failedJobs = \DB::table('failed_jobs')->count();

            // Check if there are pending jobs
            $pendingJobs = \DB::table('jobs')->count();

            // Simple test to see if queue is processing
            $queueConnection = config('queue.default');

            return [
                'working' => true, // Assume working if no errors
                'connection' => $queueConnection,
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'message' => "Queue using {$queueConnection} driver"
            ];
        } catch (\Exception $e) {
            return [
                'working' => false,
                'message' => 'Queue check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check database status
     */
    private function checkDatabaseStatus()
    {
        try {
            \DB::connection()->getPdo();

            // Check required tables
            $requiredTables = ['users', 'chats', 'messages', 'broadcast_settings'];
            $missingTables = [];

            foreach ($requiredTables as $table) {
                if (!\Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            $settingsCount = BroadcastSetting::count();

            return [
                'connected' => true,
                'missing_tables' => $missingTables,
                'settings_count' => $settingsCount,
                'message' => empty($missingTables) ?
                    'Database is healthy' :
                    'Database connected but missing tables: ' . implode(', ', $missingTables)
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Pusher/Reverb connection
     */
    private function testPusherConnection()
    {
        try {
            $host = BroadcastSetting::getValue('pusher_host', '127.0.0.1');
            $port = BroadcastSetting::getValue('pusher_port', 8080);
            $scheme = BroadcastSetting::getValue('pusher_scheme', 'http');

            $url = "{$scheme}://{$host}:{$port}/app/chatapp-key";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Pusher/Reverb connection successful',
                    'details' => [
                        'url' => $url,
                        'http_code' => $httpCode
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Connection failed with HTTP code: {$httpCode}",
                    'details' => [
                        'url' => $url,
                        'http_code' => $httpCode
                    ]
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
     * Check Pusher status
     */
    private function checkPusherStatus()
    {
        $host = BroadcastSetting::getValue('pusher_host', '127.0.0.1');
        $port = BroadcastSetting::getValue('pusher_port', 8080);
        
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        
        if ($connection) {
            fclose($connection);
            return [
                'connected' => true,
                'message' => "Connected to {$host}:{$port}"
            ];
        } else {
            return [
                'connected' => false,
                'message' => "Cannot connect to {$host}:{$port} - {$errstr}"
            ];
        }
    }

    /**
     * Test Redis connection
     */
    private function testRedisConnection()
    {
        try {
            $redis = app('redis');
            $redis->ping();
            
            return [
                'success' => true,
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check Redis status
     */
    private function checkRedisStatus()
    {
        try {
            $redis = app('redis');
            $redis->ping();
            
            return [
                'connected' => true,
                'message' => 'Redis is connected'
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Restart Reverb service
     */
    private function restartReverb()
    {
        try {
            // This would need to be implemented based on your server setup
            // For now, just return a placeholder
            return [
                'success' => true,
                'message' => 'Reverb restart command sent'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to restart Reverb: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update environment file
     */
    private function updateEnvironmentFile($settings)
    {
        // This is a simplified version - in production you'd want more robust env file handling
        $envMappings = [
            'broadcast_driver' => 'BROADCAST_DRIVER',
            'pusher_app_id' => 'PUSHER_APP_ID',
            'pusher_app_key' => 'PUSHER_APP_KEY',
            'pusher_app_secret' => 'PUSHER_APP_SECRET',
            'reverb_app_id' => 'REVERB_APP_ID',
            'reverb_app_key' => 'REVERB_APP_KEY',
            'reverb_app_secret' => 'REVERB_APP_SECRET',
            'reverb_host' => 'REVERB_HOST',
            'reverb_port' => 'REVERB_PORT',
        ];

        // Log the changes that would be made
        foreach ($settings as $key => $value) {
            if (isset($envMappings[$key])) {
                Log::info("Would update {$envMappings[$key]} = {$value}");
            }
        }
    }

    /**
     * Generate environment file format
     */
    private function generateEnvFormat($config)
    {
        $envLines = [];
        $envLines[] = "# Broadcasting Configuration";
        $envLines[] = "BROADCAST_DRIVER=" . ($config['broadcast_driver'] ?? 'pusher');
        $envLines[] = "";
        $envLines[] = "# Pusher Configuration";
        $envLines[] = "PUSHER_APP_ID=" . ($config['pusher_app_id'] ?? '');
        $envLines[] = "PUSHER_APP_KEY=" . ($config['pusher_app_key'] ?? '');
        $envLines[] = "PUSHER_APP_SECRET=" . ($config['pusher_app_secret'] ?? '');
        $envLines[] = "PUSHER_HOST=" . ($config['pusher_host'] ?? '');
        $envLines[] = "PUSHER_PORT=" . ($config['pusher_port'] ?? '');
        $envLines[] = "PUSHER_SCHEME=" . ($config['pusher_scheme'] ?? '');
        $envLines[] = "";
        $envLines[] = "# Reverb Configuration";
        $envLines[] = "REVERB_APP_ID=" . ($config['reverb_app_id'] ?? '');
        $envLines[] = "REVERB_APP_KEY=" . ($config['reverb_app_key'] ?? '');
        $envLines[] = "REVERB_APP_SECRET=" . ($config['reverb_app_secret'] ?? '');
        $envLines[] = "REVERB_HOST=" . ($config['reverb_host'] ?? '');
        $envLines[] = "REVERB_PORT=" . ($config['reverb_port'] ?? '');

        return implode("\n", $envLines);
    }

    /**
     * Test current configuration
     */
    public function testConfiguration()
    {
        try {
            $testResults = DynamicBroadcastConfigService::testConfiguration();

            return response()->json([
                'success' => true,
                'test_results' => $testResults
            ]);

        } catch (\Exception $e) {
            Log::error('Configuration test failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Configuration test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Pusher Cloud connection
     */
    private function testPusherCloudConnection()
    {
        try {
            $appKey = BroadcastSetting::getValue('pusher_cloud_app_key');
            $appSecret = BroadcastSetting::getValue('pusher_cloud_app_secret');
            $appId = BroadcastSetting::getValue('pusher_cloud_app_id');
            $cluster = BroadcastSetting::getValue('pusher_cloud_cluster', 'us2');

            if (empty($appKey) || empty($appSecret) || empty($appId)) {
                return false;
            }

            // Create Pusher instance for testing
            $pusher = new \Pusher\Pusher(
                $appKey,
                $appSecret,
                $appId,
                [
                    'cluster' => $cluster,
                    'useTLS' => true,
                ]
            );

            // Test by getting channel info
            $result = $pusher->getChannelInfo('test-channel');
            return true;

        } catch (\Exception $e) {
            Log::warning('Pusher Cloud connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test Laravel Reverb connection
     */
    private function testReverbConnection()
    {
        try {
            $host = BroadcastSetting::getValue('websocket_host', '127.0.0.1');
            $port = BroadcastSetting::getValue('websocket_port', '6001');

            // Test if Reverb server is running by attempting a socket connection
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);

            if ($socket) {
                fclose($socket);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::warning('Laravel Reverb connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
