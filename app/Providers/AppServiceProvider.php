<?php

namespace App\Providers;

use App\Models\RealtimeSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

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
        // Force correct APP_URL in production so image URLs work when deployed
        if ($this->app->environment('production') && $appUrl = config('app.url')) {
            URL::forceRootUrl(rtrim($appUrl, '/'));
            // Force HTTPS only when APP_URL uses https (e.g. behind load balancer)
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

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
