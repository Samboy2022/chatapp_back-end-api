<?php

namespace App\Services;

use App\Models\BroadcastSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DynamicBroadcastConfigService
{
    /**
     * Apply dynamic broadcast configuration based on database settings
     */
    public static function applyDynamicConfig()
    {
        try {
            // Check if broadcast settings table exists
            if (!\Schema::hasTable('broadcast_settings')) {
                Log::info('Broadcast settings table not found, using default configuration');
                return;
            }

            $serviceType = BroadcastSetting::getValue('pusher_service_type', 'reverb');
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');

            Log::info("Applying dynamic broadcast config - Driver: {$driver}, Service: {$serviceType}");

            // Set the broadcast driver
            Config::set('broadcasting.default', $driver);

            if ($driver === 'pusher') {
                if ($serviceType === 'pusher_cloud') {
                    self::configurePusherCloud();
                } else {
                    self::configureReverb();
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to apply dynamic broadcast configuration: ' . $e->getMessage());
        }
    }

    /**
     * Configure Pusher Cloud API
     */
    private static function configurePusherCloud()
    {
        $appId = BroadcastSetting::getValue('pusher_cloud_app_id', '');
        $appKey = BroadcastSetting::getValue('pusher_cloud_app_key', '');
        $appSecret = BroadcastSetting::getValue('pusher_cloud_app_secret', '');
        $cluster = BroadcastSetting::getValue('pusher_cloud_cluster', 'us2');
        $useTls = BroadcastSetting::getValue('pusher_cloud_use_tls', true);

        if (empty($appId) || empty($appKey) || empty($appSecret)) {
            Log::warning('Pusher Cloud credentials are incomplete');
            return;
        }

        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => $appKey,
            'secret' => $appSecret,
            'app_id' => $appId,
            'options' => [
                'cluster' => $cluster,
                'host' => "api-{$cluster}.pusherapp.com",
                'port' => 443,
                'scheme' => 'https',
                'encrypted' => $useTls,
                'useTLS' => $useTls,
            ],
            'client_options' => [],
        ]);

        // Set environment variables for frontend
        Config::set('app.pusher_app_key', $appKey);
        Config::set('app.pusher_app_cluster', $cluster);
        Config::set('app.pusher_use_tls', $useTls);

        Log::info("Configured Pusher Cloud API - Cluster: {$cluster}");
    }

    /**
     * Configure Laravel Reverb (Self-hosted)
     */
    private static function configureReverb()
    {
        $appId = BroadcastSetting::getValue('pusher_app_id', 'chatapp-id');
        $appKey = BroadcastSetting::getValue('pusher_app_key', 'chatapp-key');
        $appSecret = BroadcastSetting::getValue('pusher_app_secret', 'chatapp-secret');
        $host = BroadcastSetting::getValue('pusher_host', '127.0.0.1');
        $port = BroadcastSetting::getValue('pusher_port', 8080);
        $scheme = BroadcastSetting::getValue('pusher_scheme', 'http');

        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => $appKey,
            'secret' => $appSecret,
            'app_id' => $appId,
            'options' => [
                'cluster' => null, // No cluster for Reverb
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'encrypted' => $scheme === 'https',
                'useTLS' => $scheme === 'https',
            ],
            'client_options' => [],
        ]);

        // Also configure Reverb connection
        Config::set('broadcasting.connections.reverb', [
            'driver' => 'reverb',
            'key' => $appKey,
            'secret' => $appSecret,
            'app_id' => $appId,
            'options' => [
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'useTLS' => $scheme === 'https',
            ],
        ]);

        // Set environment variables for frontend
        Config::set('app.pusher_app_key', $appKey);
        Config::set('app.pusher_host', $host);
        Config::set('app.pusher_port', $port);
        Config::set('app.pusher_scheme', $scheme);

        Log::info("Configured Laravel Reverb - Host: {$host}:{$port}");
    }

    /**
     * Get current configuration for frontend
     */
    public static function getFrontendConfig()
    {
        try {
            $serviceType = BroadcastSetting::getValue('pusher_service_type', 'reverb');
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');
            $enabled = BroadcastSetting::getValue('broadcast_enabled', true);

            $config = [
                'enabled' => $enabled,
                'driver' => $driver,
                'service_type' => $serviceType,
            ];

            if ($driver === 'pusher') {
                if ($serviceType === 'pusher_cloud') {
                    $config['pusher'] = [
                        'key' => BroadcastSetting::getValue('pusher_cloud_app_key', ''),
                        'cluster' => BroadcastSetting::getValue('pusher_cloud_cluster', 'us2'),
                        'forceTLS' => BroadcastSetting::getValue('pusher_cloud_use_tls', true),
                        'encrypted' => BroadcastSetting::getValue('pusher_cloud_use_tls', true),
                    ];
                } else {
                    $config['pusher'] = [
                        'key' => BroadcastSetting::getValue('pusher_app_key', 'chatapp-key'),
                        'wsHost' => BroadcastSetting::getValue('pusher_host', '127.0.0.1'),
                        'wsPort' => BroadcastSetting::getValue('pusher_port', 8080),
                        'forceTLS' => BroadcastSetting::getValue('pusher_scheme', 'http') === 'https',
                        'encrypted' => BroadcastSetting::getValue('pusher_scheme', 'http') === 'https',
                        'disableStats' => true,
                        'enabledTransports' => ['ws'],
                    ];
                }
            }

            $config['auth_endpoint'] = url('/broadcasting/auth');

            return $config;

        } catch (\Exception $e) {
            Log::error('Failed to get frontend config: ' . $e->getMessage());
            return [
                'enabled' => false,
                'driver' => 'null',
                'service_type' => 'reverb',
                'error' => 'Configuration error'
            ];
        }
    }

    /**
     * Test current configuration
     */
    public static function testConfiguration()
    {
        try {
            $serviceType = BroadcastSetting::getValue('pusher_service_type', 'reverb');
            $driver = BroadcastSetting::getValue('broadcast_driver', 'pusher');
            $enabled = BroadcastSetting::getValue('broadcast_enabled', true);

            $result = [
                'driver' => $driver,
                'service_type' => $serviceType,
                'enabled' => $enabled,
                'tests' => []
            ];

            if (!$enabled) {
                $result['tests']['enabled'] = [
                    'status' => 'warning',
                    'message' => 'Broadcasting is disabled'
                ];
                return $result;
            }

            if ($driver === 'pusher') {
                if ($serviceType === 'pusher_cloud') {
                    $result['tests'] = self::testPusherCloud();
                } else {
                    $result['tests'] = self::testReverb();
                }
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'driver' => 'unknown',
                'service_type' => 'unknown',
                'enabled' => false,
                'tests' => [
                    'error' => [
                        'status' => 'error',
                        'message' => 'Configuration test failed: ' . $e->getMessage()
                    ]
                ]
            ];
        }
    }

    /**
     * Test Pusher Cloud configuration
     */
    private static function testPusherCloud()
    {
        $tests = [];

        $appId = BroadcastSetting::getValue('pusher_cloud_app_id', '');
        $appKey = BroadcastSetting::getValue('pusher_cloud_app_key', '');
        $appSecret = BroadcastSetting::getValue('pusher_cloud_app_secret', '');
        $cluster = BroadcastSetting::getValue('pusher_cloud_cluster', 'us2');

        // Test credentials
        if (empty($appId) || empty($appKey) || empty($appSecret)) {
            $tests['credentials'] = [
                'status' => 'error',
                'message' => 'Pusher Cloud credentials are incomplete'
            ];
        } else {
            $tests['credentials'] = [
                'status' => 'success',
                'message' => 'Pusher Cloud credentials are configured'
            ];
        }

        // Test cluster
        $validClusters = ['us2', 'us3', 'eu', 'ap1', 'ap2', 'ap3', 'ap4'];
        if (in_array($cluster, $validClusters)) {
            $tests['cluster'] = [
                'status' => 'success',
                'message' => "Using valid cluster: {$cluster}"
            ];
        } else {
            $tests['cluster'] = [
                'status' => 'warning',
                'message' => "Unknown cluster: {$cluster}"
            ];
        }

        return $tests;
    }

    /**
     * Test Reverb configuration
     */
    private static function testReverb()
    {
        $tests = [];

        $host = BroadcastSetting::getValue('pusher_host', '127.0.0.1');
        $port = BroadcastSetting::getValue('pusher_port', 8080);
        $appKey = BroadcastSetting::getValue('pusher_app_key', 'chatapp-key');

        // Test socket connection
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($connection) {
                fclose($connection);
                $tests['socket'] = [
                    'status' => 'success',
                    'message' => "Reverb server is accessible on {$host}:{$port}"
                ];
            } else {
                $tests['socket'] = [
                    'status' => 'error',
                    'message' => "Cannot connect to Reverb server on {$host}:{$port} - {$errstr}"
                ];
            }
        } catch (\Exception $e) {
            $tests['socket'] = [
                'status' => 'error',
                'message' => "Socket test failed: " . $e->getMessage()
            ];
        }

        // Test HTTP endpoint
        try {
            $url = "http://{$host}:{$port}/app/{$appKey}";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                $tests['http'] = [
                    'status' => 'success',
                    'message' => 'Reverb HTTP endpoint is accessible'
                ];
            } else {
                $tests['http'] = [
                    'status' => 'warning',
                    'message' => 'Reverb HTTP endpoint is not accessible'
                ];
            }
        } catch (\Exception $e) {
            $tests['http'] = [
                'status' => 'error',
                'message' => 'HTTP test failed: ' . $e->getMessage()
            ];
        }

        return $tests;
    }
}
