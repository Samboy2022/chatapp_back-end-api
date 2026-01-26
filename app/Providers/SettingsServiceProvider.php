<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only share settings if the settings table exists
        if (Schema::hasTable('settings')) {
            try {
                // Share all settings with all views
                View::composer('*', function ($view) {
                    $settings = Setting::all()->pluck('value', 'key')->toArray();
                    
                    // Fallback defaults for critical settings
                    $defaults = [
                        'app_name' => config('app.name', 'Farmers Network'),
                        'app_description' => 'Connect & Collaborate',
                        'app_url' => config('app.url'),
                        'admin_email' => 'admin@farmersnetwork.com',
                        'logo_url' => null,
                    ];
                    
                    $view->with('appSettings', array_merge($defaults, $settings));
                });
            } catch (\Exception $e) {
                // Fallback to defaults
                View::composer('*', function ($view) {
                    $view->with('appSettings', [
                        'app_name' => config('app.name', 'Farmers Network'),
                        'app_description' => 'Connect & Collaborate',
                        'app_url' => config('app.url'),
                        'admin_email' => 'admin@farmersnetwork.com',
                        'logo_url' => null,
                    ]);
                });
            }
        }
    }
}
