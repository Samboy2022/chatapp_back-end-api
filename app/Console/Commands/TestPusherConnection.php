<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

class TestPusherConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pusher:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Pusher Cloud connection and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Pusher Cloud Connection...');

        try {
            // Test basic configuration
            $this->info('📋 Checking configuration...');
            $driver = config('broadcasting.default');
            $this->line("Broadcasting driver: {$driver}");

            if ($driver !== 'pusher') {
                $this->error('❌ Broadcasting driver is not set to pusher');
                return 1;
            }

            // Test Pusher credentials
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $cluster = config('broadcasting.connections.pusher.options.cluster');

            if (!$appId || !$key || !$secret || !$cluster) {
                $this->error('❌ Missing Pusher credentials in configuration');
                return 1;
            }

            $this->line("App ID: {$appId}");
            $this->line("Key: {$key}");
            $this->line("Cluster: {$cluster}");

            // Test Pusher connection
            $this->info('🔗 Testing Pusher connection...');

            $pusher = new Pusher(
                $key,
                $secret,
                $appId,
                [
                    'cluster' => $cluster,
                    'useTLS' => true
                ]
            );

            // Test by triggering a simple event
            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Hello from Laravel!',
                'timestamp' => now()->toISOString()
            ]);

            if ($result) {
                $this->info('✅ Pusher connection successful!');
                $this->line('Test event sent to test-channel');
                return 0;
            } else {
                $this->error('❌ Failed to send test event');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Pusher connection failed: ' . $e->getMessage());
            return 1;
        }
    }
}
