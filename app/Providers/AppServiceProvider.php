<?php

namespace App\Providers;

use App\Models\RealtimeSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Apply realtime broadcast settings
        $this->configureBroadcasting();
    }

    /**
     * Configure broadcasting based on realtime settings
     */
    private function configureBroadcasting(): void
    {
        // Only apply settings if not in console and table exists
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            // Check if realtime_settings table exists
            if (!Schema::hasTable('realtime_settings')) {
                Log::info('Realtime settings table not found, using environment defaults');
                return;
            }

            // Apply realtime settings to Laravel configuration
            RealtimeSetting::applyToConfig();

        } catch (\Exception $e) {
            Log::error('Failed to configure broadcasting from realtime settings: ' . $e->getMessage());
            // Fallback to environment configuration
        }
    }
}
