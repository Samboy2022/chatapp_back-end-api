<?php

namespace App\Providers;

use App\Services\BroadcastConfigService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class BroadcastConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the broadcast config service
        $this->app->singleton(BroadcastConfigService::class, function ($app) {
            return new BroadcastConfigService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apply broadcast settings on application boot (only if table exists)
        if ($this->app->runningInConsole() === false && $this->tableExists()) {
            try {
                BroadcastConfigService::applySettings();
            } catch (\Exception $e) {
                // Log error but don't break the application
                logger()->error('Failed to apply broadcast settings: ' . $e->getMessage());
            }
        }

        // Share broadcast config with views
        View::composer('*', function ($view) {
            try {
                if ($this->tableExists()) {
                    $broadcastConfig = BroadcastConfigService::getClientConfig();
                    $view->with('broadcastConfig', $broadcastConfig);
                } else {
                    $view->with('broadcastConfig', ['enabled' => false, 'driver' => 'pusher']);
                }
            } catch (\Exception $e) {
                $view->with('broadcastConfig', ['enabled' => false, 'driver' => 'pusher']);
            }
        });
    }

    /**
     * Check if broadcast_settings table exists
     */
    private function tableExists(): bool
    {
        try {
            return \Schema::hasTable('broadcast_settings');
        } catch (\Exception $e) {
            return false;
        }
    }
}
