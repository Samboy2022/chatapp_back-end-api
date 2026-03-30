<?php

namespace App\Http\Controllers;

use App\Models\BroadcastSetting;
use App\Services\BroadcastConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * General health check
     */
    public function index()
    {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => config('app.version', '1.0.0'),
                'environment' => config('app.env'),
                'checks' => [
                    'database' => $this->checkDatabase(),
                    'cache' => $this->checkCache(),
                    'broadcasting' => $this->checkBroadcasting(),
                    'storage' => $this->checkStorage(),
                ]
            ];

            // Determine overall status
            $allHealthy = collect($health['checks'])->every(function ($check) {
                return $check['status'] === 'healthy';
            });

            $health['status'] = $allHealthy ? 'healthy' : 'degraded';

            return response()->json($health, $allHealthy ? 200 : 503);

        } catch (\Exception $e) {
            Log::error('Health check failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detailed broadcast system health check
     */
    public function broadcast()
    {
        try {
            $broadcastHealth = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'checks' => [
                    'configuration' => $this->checkBroadcastConfiguration(),
                    'database_settings' => $this->checkBroadcastDatabaseSettings(),
                    'reverb_server' => $this->checkReverbServer(),
                    'channels' => $this->checkChannelConfiguration(),
                    'authentication' => $this->checkBroadcastAuthentication(),
                ]
            ];

            // Determine overall broadcast status
            $allHealthy = collect($broadcastHealth['checks'])->every(function ($check) {
                return $check['status'] === 'healthy';
            });

            $broadcastHealth['status'] = $allHealthy ? 'healthy' : 'degraded';

            return response()->json($broadcastHealth, $allHealthy ? 200 : 503);

        } catch (\Exception $e) {
            Log::error('Broadcast health check failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $tables = ['users', 'chats', 'messages', 'broadcast_settings'];
            $missingTables = [];

            foreach ($tables as $table) {
                if (!\Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                return [
                    'status' => 'healthy',
                    'message' => 'Database connection successful, all tables present'
                ];
            } else {
                return [
                    'status' => 'degraded',
                    'message' => 'Database connected but missing tables: ' . implode(', ', $missingTables)
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache()
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache system working correctly'
                ];
            } else {
                return [
                    'status' => 'degraded',
                    'message' => 'Cache system not working properly'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache system failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check broadcasting system
     */
    private function checkBroadcasting()
    {
        try {
            $driver = config('broadcasting.default');
            $enabled = BroadcastSetting::getValue('broadcast_enabled', true);

            if (!$enabled) {
                return [
                    'status' => 'degraded',
                    'message' => 'Broadcasting is disabled'
                ];
            }

            $validation = BroadcastConfigService::validateConfig();
            
            return [
                'status' => $validation['valid'] ? 'healthy' : 'degraded',
                'message' => $validation['message'],
                'driver' => $driver,
                'errors' => $validation['errors'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Broadcasting check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check storage system
     */
    private function checkStorage()
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'Health check test file';
            
            \Storage::disk('public')->put($testFile, $testContent);
            $retrieved = \Storage::disk('public')->get($testFile);
            \Storage::disk('public')->delete($testFile);

            if ($retrieved === $testContent) {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage system working correctly'
                ];
            } else {
                return [
                    'status' => 'degraded',
                    'message' => 'Storage system not working properly'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage system failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check broadcast configuration
     */
    private function checkBroadcastConfiguration()
    {
        try {
            $config = config('broadcasting');
            $driver = $config['default'] ?? 'null';
            
            if ($driver === 'null') {
                return [
                    'status' => 'degraded',
                    'message' => 'Broadcasting driver is set to null'
                ];
            }

            $driverConfig = $config['connections'][$driver] ?? null;
            if (!$driverConfig) {
                return [
                    'status' => 'unhealthy',
                    'message' => "No configuration found for driver: {$driver}"
                ];
            }

            return [
                'status' => 'healthy',
                'message' => "Broadcasting configured with driver: {$driver}",
                'driver' => $driver
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Broadcast configuration check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check broadcast database settings
     */
    private function checkBroadcastDatabaseSettings()
    {
        try {
            if (!\Schema::hasTable('broadcast_settings')) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'broadcast_settings table does not exist'
                ];
            }

            $settingsCount = BroadcastSetting::count();
            $activeSettings = BroadcastSetting::where('is_active', true)->count();

            return [
                'status' => 'healthy',
                'message' => "Found {$activeSettings} active settings out of {$settingsCount} total",
                'total_settings' => $settingsCount,
                'active_settings' => $activeSettings
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Broadcast database settings check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check Reverb server
     */
    private function checkReverbServer()
    {
        try {
            $host = BroadcastSetting::getValue('reverb_host', '0.0.0.0');
            $port = BroadcastSetting::getValue('reverb_port', 8080);
            $appKey = BroadcastSetting::getValue('reverb_app_key', 'chatapp-key');

            $url = "http://{$host}:{$port}/app/{$appKey}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                return [
                    'status' => 'healthy',
                    'message' => 'Reverb server is accessible',
                    'url' => $url
                ];
            } else {
                return [
                    'status' => 'degraded',
                    'message' => 'Reverb server is not accessible',
                    'url' => $url
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Reverb server check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check channel configuration
     */
    private function checkChannelConfiguration()
    {
        try {
            $channelsFile = base_path('routes/channels.php');
            
            if (!file_exists($channelsFile)) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'channels.php file not found'
                ];
            }

            $content = file_get_contents($channelsFile);
            $hasPrivateChannels = strpos($content, 'Broadcast::channel') !== false;
            
            return [
                'status' => $hasPrivateChannels ? 'healthy' : 'degraded',
                'message' => $hasPrivateChannels ? 'Channel configuration found' : 'No channel configuration found'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Channel configuration check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check broadcast authentication
     */
    private function checkBroadcastAuthentication()
    {
        try {
            $authEndpoint = route('broadcasting.auth', [], false);
            
            return [
                'status' => 'healthy',
                'message' => 'Broadcast authentication endpoint configured',
                'endpoint' => $authEndpoint
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Broadcast authentication check failed: ' . $e->getMessage()
            ];
        }
    }
}
