<?php

namespace Database\Seeders;

use App\Models\BroadcastSetting;
use Illuminate\Database\Seeder;

class BroadcastSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Broadcasting Settings
            [
                'key' => 'broadcast_driver',
                'value' => 'pusher',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Broadcast Driver',
                'description' => 'The broadcasting driver to use (pusher, redis, log, null)',
                'is_required' => true,
                'options' => [
                    'pusher' => 'Pusher (Cloud/Reverb)',
                    'redis' => 'Redis',
                    'log' => 'Log (Development)',
                    'null' => 'Disabled'
                ],
                'validation_rules' => ['required', 'in:pusher,redis,log,null'],
                'sort_order' => 1,
            ],
            [
                'key' => 'pusher_service_type',
                'value' => 'reverb',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Pusher Service Type',
                'description' => 'Choose between Pusher Cloud API or Laravel Reverb (self-hosted)',
                'is_required' => true,
                'options' => [
                    'pusher_cloud' => 'Pusher Cloud API (pusher.com)',
                    'reverb' => 'Laravel Reverb (Self-hosted)'
                ],
                'validation_rules' => ['required', 'in:pusher_cloud,reverb'],
                'sort_order' => 2,
            ],
            [
                'key' => 'broadcast_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Enable Broadcasting',
                'description' => 'Enable or disable real-time broadcasting features',
                'is_required' => true,
                'sort_order' => 3,
            ],

            // Pusher Cloud API Settings (pusher.com)
            [
                'key' => 'pusher_cloud_app_id',
                'value' => '',
                'type' => 'string',
                'group' => 'pusher_cloud',
                'label' => 'Pusher Cloud App ID',
                'description' => 'Your Pusher Cloud application ID from pusher.com dashboard',
                'is_required' => false,
                'validation_rules' => ['string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'pusher_cloud_app_key',
                'value' => '',
                'type' => 'string',
                'group' => 'pusher_cloud',
                'label' => 'Pusher Cloud App Key',
                'description' => 'Your Pusher Cloud application key from pusher.com dashboard',
                'is_required' => false,
                'validation_rules' => ['string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'pusher_cloud_app_secret',
                'value' => '',
                'type' => 'string',
                'group' => 'pusher_cloud',
                'label' => 'Pusher Cloud App Secret',
                'description' => 'Your Pusher Cloud application secret from pusher.com dashboard',
                'is_required' => false,
                'is_sensitive' => true,
                'validation_rules' => ['string'],
                'sort_order' => 3,
            ],
            [
                'key' => 'pusher_cloud_cluster',
                'value' => 'us2',
                'type' => 'string',
                'group' => 'pusher_cloud',
                'label' => 'Pusher Cloud Cluster',
                'description' => 'Your Pusher Cloud cluster (e.g., us2, eu, ap1)',
                'is_required' => false,
                'options' => [
                    'us2' => 'US East (us2)',
                    'us3' => 'US West (us3)',
                    'eu' => 'Europe (eu)',
                    'ap1' => 'Asia Pacific (ap1)',
                    'ap2' => 'Asia Pacific 2 (ap2)',
                    'ap3' => 'Asia Pacific 3 (ap3)',
                    'ap4' => 'Asia Pacific 4 (ap4)'
                ],
                'validation_rules' => ['string'],
                'sort_order' => 4,
            ],
            [
                'key' => 'pusher_cloud_use_tls',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'pusher_cloud',
                'label' => 'Use TLS/SSL',
                'description' => 'Enable TLS/SSL for Pusher Cloud connections (recommended)',
                'is_required' => false,
                'sort_order' => 5,
            ],

            // Laravel Reverb Settings (Self-hosted)
            [
                'key' => 'pusher_app_id',
                'value' => 'chatapp-id',
                'type' => 'string',
                'group' => 'pusher',
                'label' => 'Reverb App ID',
                'description' => 'Laravel Reverb application ID (for self-hosted)',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'pusher_app_key',
                'value' => 'chatapp-key',
                'type' => 'string',
                'group' => 'pusher',
                'label' => 'Reverb App Key',
                'description' => 'Laravel Reverb application key (for self-hosted)',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'pusher_app_secret',
                'value' => 'chatapp-secret',
                'type' => 'string',
                'group' => 'pusher',
                'label' => 'Reverb App Secret',
                'description' => 'Laravel Reverb application secret (for self-hosted)',
                'is_required' => true,
                'is_sensitive' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 3,
            ],
            [
                'key' => 'pusher_host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'group' => 'pusher',
                'label' => 'Reverb Host',
                'description' => 'Laravel Reverb server host (for self-hosted)',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 4,
            ],
            [
                'key' => 'pusher_port',
                'value' => '8080',
                'type' => 'integer',
                'group' => 'pusher',
                'label' => 'Reverb Port',
                'description' => 'Laravel Reverb server port (for self-hosted)',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:1', 'max:65535'],
                'sort_order' => 5,
            ],
            [
                'key' => 'pusher_scheme',
                'value' => 'http',
                'type' => 'string',
                'group' => 'pusher',
                'label' => 'Reverb Scheme',
                'description' => 'Protocol scheme for Reverb (http or https)',
                'is_required' => true,
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS'
                ],
                'validation_rules' => ['required', 'in:http,https'],
                'sort_order' => 6,
            ],

            // Reverb Specific Settings
            [
                'key' => 'reverb_app_id',
                'value' => 'chatapp-id',
                'type' => 'string',
                'group' => 'reverb',
                'label' => 'Reverb App ID',
                'description' => 'Laravel Reverb application ID',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'reverb_app_key',
                'value' => 'chatapp-key',
                'type' => 'string',
                'group' => 'reverb',
                'label' => 'Reverb App Key',
                'description' => 'Laravel Reverb application key',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'reverb_app_secret',
                'value' => 'chatapp-secret',
                'type' => 'string',
                'group' => 'reverb',
                'label' => 'Reverb App Secret',
                'description' => 'Laravel Reverb application secret',
                'is_required' => true,
                'is_sensitive' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 3,
            ],
            [
                'key' => 'reverb_host',
                'value' => '0.0.0.0',
                'type' => 'string',
                'group' => 'reverb',
                'label' => 'Reverb Host',
                'description' => 'Laravel Reverb server host',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 4,
            ],
            [
                'key' => 'reverb_port',
                'value' => '8080',
                'type' => 'integer',
                'group' => 'reverb',
                'label' => 'Reverb Port',
                'description' => 'Laravel Reverb server port',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:1', 'max:65535'],
                'sort_order' => 5,
            ],

            // WebSocket Client Settings
            [
                'key' => 'websocket_host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'group' => 'websocket',
                'label' => 'WebSocket Host',
                'description' => 'WebSocket host for client connections',
                'is_required' => true,
                'validation_rules' => ['required', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'websocket_port',
                'value' => '6001',
                'type' => 'integer',
                'group' => 'websocket',
                'label' => 'WebSocket Port',
                'description' => 'WebSocket port for client connections',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:1', 'max:65535'],
                'sort_order' => 2,
            ],
            [
                'key' => 'websocket_force_tls',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'websocket',
                'label' => 'Force TLS',
                'description' => 'Force TLS/SSL for WebSocket connections',
                'is_required' => false,
                'sort_order' => 3,
            ],

            // Performance Settings
            [
                'key' => 'max_connections',
                'value' => '1000',
                'type' => 'integer',
                'group' => 'performance',
                'label' => 'Max Connections',
                'description' => 'Maximum number of concurrent WebSocket connections',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:1'],
                'sort_order' => 1,
            ],
            [
                'key' => 'connection_timeout',
                'value' => '30',
                'type' => 'integer',
                'group' => 'performance',
                'label' => 'Connection Timeout',
                'description' => 'Connection timeout in seconds',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:5', 'max:300'],
                'sort_order' => 2,
            ],
            [
                'key' => 'ping_interval',
                'value' => '25',
                'type' => 'integer',
                'group' => 'performance',
                'label' => 'Ping Interval',
                'description' => 'Ping interval in seconds',
                'is_required' => true,
                'validation_rules' => ['required', 'integer', 'min:5', 'max:60'],
                'sort_order' => 3,
            ],
        ];

        foreach ($settings as $setting) {
            BroadcastSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
