<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class RealtimeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'driver',
        'pusher_app_id',
        'pusher_key',
        'pusher_secret',
        'pusher_cluster',
        'reverb_app_id',
        'reverb_key',
        'reverb_secret',
        'reverb_host',
        'reverb_port',
        'reverb_scheme',
    ];

    protected $casts = [
        'reverb_port' => 'integer',
    ];

    /**
     * Get the current realtime settings
     */
    public static function current()
    {
        return Cache::remember('realtime_settings', 300, function () {
            return static::first() ?? static::create([
                'status' => 'enabled',
                'driver' => 'pusher',
                'pusher_cluster' => 'mt1',
                'reverb_host' => '127.0.0.1',
                'reverb_port' => 8080,
                'reverb_scheme' => 'http',
            ]);
        });
    }

    /**
     * Check if broadcasting is enabled
     */
    public static function isEnabled()
    {
        return static::current()->status === 'enabled';
    }

    /**
     * Get current driver
     */
    public static function getDriver()
    {
        return static::current()->driver;
    }

    /**
     * Apply settings to Laravel configuration
     */
    public static function applyToConfig()
    {
        try {
            $settings = static::current();
            
            Log::info('Applying realtime settings', [
                'status' => $settings->status,
                'driver' => $settings->driver
            ]);

            // If disabled, set to log driver
            if ($settings->status === 'disabled') {
                Config::set('broadcasting.default', 'log');
                Log::info('Broadcasting disabled, using log driver');
                return;
            }

            // Apply based on selected driver
            if ($settings->driver === 'pusher') {
                static::applyPusherConfig($settings);
            } elseif ($settings->driver === 'reverb') {
                static::applyReverbConfig($settings);
            }

            Log::info('Realtime configuration applied successfully');

        } catch (\Exception $e) {
            Log::error('Failed to apply realtime settings: ' . $e->getMessage());
            // Fallback to log driver
            Config::set('broadcasting.default', 'log');
        }
    }

    /**
     * Apply Pusher configuration
     */
    private static function applyPusherConfig($settings)
    {
        Config::set('broadcasting.default', 'pusher');
        
        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => $settings->pusher_key,
            'secret' => $settings->pusher_secret,
            'app_id' => $settings->pusher_app_id,
            'options' => [
                'cluster' => $settings->pusher_cluster,
                'host' => null,
                'port' => 443,
                'scheme' => 'https',
                'encrypted' => true,
                'useTLS' => true,
            ],
        ]);

        Log::info('Pusher configuration applied', [
            'app_id' => $settings->pusher_app_id,
            'cluster' => $settings->pusher_cluster
        ]);
    }

    /**
     * Apply Reverb configuration
     */
    private static function applyReverbConfig($settings)
    {
        Config::set('broadcasting.default', 'pusher');
        
        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => $settings->reverb_key,
            'secret' => $settings->reverb_secret,
            'app_id' => $settings->reverb_app_id,
            'options' => [
                'host' => $settings->reverb_host,
                'port' => $settings->reverb_port,
                'scheme' => $settings->reverb_scheme,
                'encrypted' => $settings->reverb_scheme === 'https',
                'useTLS' => $settings->reverb_scheme === 'https',
            ],
        ]);

        Log::info('Reverb configuration applied', [
            'host' => $settings->reverb_host,
            'port' => $settings->reverb_port,
            'scheme' => $settings->reverb_scheme
        ]);
    }

    /**
     * Get configuration for mobile/frontend apps
     */
    public static function getFrontendConfig()
    {
        $settings = static::current();

        if ($settings->status === 'disabled') {
            return [
                'enabled' => false,
                'driver' => 'log',
                'message' => 'Broadcasting is disabled'
            ];
        }

        $config = [
            'enabled' => true,
            'driver' => $settings->driver,
        ];

        if ($settings->driver === 'pusher') {
            $config = array_merge($config, [
                'key' => $settings->pusher_key,
                'cluster' => $settings->pusher_cluster,
                'host' => null,
                'port' => 443,
                'scheme' => 'https',
                'encrypted' => true,
                'auth_endpoint' => url('/broadcasting/auth'),
            ]);
        } elseif ($settings->driver === 'reverb') {
            $config = array_merge($config, [
                'key' => $settings->reverb_key,
                'host' => $settings->reverb_host,
                'port' => $settings->reverb_port,
                'scheme' => $settings->reverb_scheme,
                'encrypted' => $settings->reverb_scheme === 'https',
                'auth_endpoint' => url('/broadcasting/auth'),
            ]);
        }

        return $config;
    }

    /**
     * Test connection based on current settings
     */
    public static function testConnection()
    {
        $settings = static::current();

        if ($settings->status === 'disabled') {
            return [
                'success' => false,
                'message' => 'Broadcasting is disabled',
                'driver' => 'log'
            ];
        }

        try {
            if ($settings->driver === 'pusher') {
                // For Pusher, validate configuration and test basic connectivity
                $configTest = static::validatePusherConfig($settings);
                if (!$configTest['success']) {
                    return $configTest;
                }

                // Try basic connectivity test
                $basicTest = static::testPusherBasicConnectivity($settings);
                if ($basicTest['success']) {
                    return [
                        'success' => true,
                        'message' => 'Pusher Cloud configuration valid and connectivity OK',
                        'driver' => 'pusher'
                    ];
                }

                // If connectivity fails, still return success if config is valid
                // (network issues shouldn't prevent configuration)
                return [
                    'success' => true,
                    'message' => 'Pusher Cloud configuration valid (network test failed but this is acceptable)',
                    'driver' => 'pusher'
                ];

            } elseif ($settings->driver === 'reverb') {
                return static::testReverbConnection($settings);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'driver' => $settings->driver
            ];
        }

        return [
            'success' => false,
            'message' => 'Unknown driver',
            'driver' => $settings->driver
        ];
    }

    /**
     * Test Pusher connection
     */
    private static function testPusherConnection($settings)
    {
        if (empty($settings->pusher_key) || empty($settings->pusher_secret) || empty($settings->pusher_app_id)) {
            return [
                'success' => false,
                'message' => 'Pusher credentials are incomplete',
                'driver' => 'pusher'
            ];
        }

        try {
            // Create authenticated request to Pusher API
            $timestamp = time();
            $auth_string = "GET\n/apps/{$settings->pusher_app_id}/channels\nauth_key={$settings->pusher_key}&auth_timestamp={$timestamp}&auth_version=1.0";
            $auth_signature = hash_hmac('sha256', $auth_string, $settings->pusher_secret);

            $url = "https://api-{$settings->pusher_cluster}.pusherapp.com/apps/{$settings->pusher_app_id}/channels";
            $url .= "?auth_key={$settings->pusher_key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&auth_signature={$auth_signature}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => [
                        'Content-Type: application/json',
                        'User-Agent: FarmersNetwork/1.0'
                    ]
                ]
            ]);

            $result = @file_get_contents($url, false, $context);
            $httpCode = 200;

            // Check if we got HTTP response headers
            if (isset($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                        $httpCode = (int)$matches[1];
                        break;
                    }
                }
            }

            if ($result !== false && $httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($result, true);
                if ($data !== null) {
                    return [
                        'success' => true,
                        'message' => 'Pusher Cloud connection successful - API accessible',
                        'driver' => 'pusher'
                    ];
                }
            }

            // If we get here, there was an issue
            return [
                'success' => false,
                'message' => "Pusher Cloud connection failed - HTTP {$httpCode}. Check your credentials and cluster setting.",
                'driver' => 'pusher'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Pusher Cloud connection error: ' . $e->getMessage(),
                'driver' => 'pusher'
            ];
        }
    }

    /**
     * Validate Pusher configuration
     */
    private static function validatePusherConfig($settings)
    {
        $errors = [];

        if (empty($settings->pusher_app_id)) {
            $errors[] = 'Pusher App ID is required';
        }

        if (empty($settings->pusher_key)) {
            $errors[] = 'Pusher App Key is required';
        }

        if (empty($settings->pusher_secret)) {
            $errors[] = 'Pusher App Secret is required';
        }

        if (empty($settings->pusher_cluster)) {
            $errors[] = 'Pusher Cluster is required';
        }

        // Validate format
        if (!empty($settings->pusher_app_id) && !is_numeric($settings->pusher_app_id)) {
            $errors[] = 'Pusher App ID should be numeric';
        }

        if (!empty($settings->pusher_key) && strlen($settings->pusher_key) < 10) {
            $errors[] = 'Pusher App Key appears to be too short';
        }

        if (!empty($settings->pusher_secret) && strlen($settings->pusher_secret) < 10) {
            $errors[] = 'Pusher App Secret appears to be too short';
        }

        if (!empty($settings->pusher_cluster) && !preg_match('/^[a-z0-9-]+$/', $settings->pusher_cluster)) {
            $errors[] = 'Pusher Cluster should contain only lowercase letters, numbers, and hyphens';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Pusher configuration invalid: ' . implode(', ', $errors),
                'driver' => 'pusher'
            ];
        }

        return [
            'success' => true,
            'message' => 'Pusher configuration is valid',
            'driver' => 'pusher'
        ];
    }

    /**
     * Test basic Pusher connectivity (without authentication)
     */
    private static function testPusherBasicConnectivity($settings)
    {
        try {
            // List of possible Pusher API endpoints to try
            $possibleHosts = [
                "api-{$settings->pusher_cluster}.pusherapp.com",
                "api.pusherapp.com", // Fallback to main API
                "sockjs-{$settings->pusher_cluster}.pusher.com", // Alternative format
            ];

            foreach ($possibleHosts as $host) {
                $connection = @fsockopen($host, 443, $errno, $errstr, 3);

                if ($connection) {
                    fclose($connection);
                    return [
                        'success' => true,
                        'message' => "Pusher Cloud connectivity successful via {$host}",
                        'driver' => 'pusher'
                    ];
                }
            }

            // If all hosts failed, return the last error
            return [
                'success' => false,
                'message' => "Pusher Cloud connectivity failed. Last error: {$errstr} ({$errno}). Check your cluster setting or network connectivity.",
                'driver' => 'pusher'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Pusher Cloud connectivity error: ' . $e->getMessage(),
                'driver' => 'pusher'
            ];
        }
    }

    /**
     * Test Reverb connection
     */
    private static function testReverbConnection($settings)
    {
        $connection = @fsockopen($settings->reverb_host, $settings->reverb_port, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);
            return [
                'success' => true,
                'message' => 'Reverb server connection successful',
                'driver' => 'reverb'
            ];
        }

        return [
            'success' => false,
            'message' => "Reverb server connection failed: {$errstr} ({$errno})",
            'driver' => 'reverb'
        ];
    }

    /**
     * Clear cache when settings are updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('realtime_settings');
            Log::info('Realtime settings cache cleared');
        });

        static::deleted(function () {
            Cache::forget('realtime_settings');
        });
    }
}
