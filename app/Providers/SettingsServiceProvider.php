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
                // Share common settings with all views
                View::composer('*', function ($view) {
                    $view->with('appSettings', [
                        'app_name' => Setting::get('app_name', config('app.name', 'Farmers Network')),
                        'app_description' => Setting::get('app_description', 'Connect & Collaborate'),
                        'app_url' => Setting::get('app_url', config('app.url')),
                        'admin_email' => Setting::get('admin_email', 'admin@example.com'),
                        'logo_url' => Setting::get('logo_url', null),
                    ]);
                });
            } catch (\Exception $e) {
                // If settings table doesn't exist yet, use defaults
                View::composer('*', function ($view) {
                    $view->with('appSettings', [
                        'app_name' => config('app.name', 'Farmers Network'),
                        'app_description' => 'Connect & Collaborate',
                        'app_url' => config('app.url'),
                        'admin_email' => 'admin@example.com',
                        'logo_url' => null,
                    ]);
                });
            }
        }
    }
}
