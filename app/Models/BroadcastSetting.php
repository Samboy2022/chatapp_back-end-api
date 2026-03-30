<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class BroadcastSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_required',
        'is_sensitive',
        'validation_rules',
        'options',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_sensitive' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
        'options' => 'array',
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are saved or deleted
        static::saved(function ($setting) {
            Cache::forget('broadcast_settings');
            Cache::forget('broadcast_config');
            Log::info("Cache cleared after saving setting: {$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget('broadcast_settings');
            Cache::forget('broadcast_config');
            Log::info("Cache cleared after deleting setting: {$setting->key}");
        });
    }

    /**
     * Get the value with proper type casting
     */
    public function getTypedValueAttribute()
    {
        if ($this->is_sensitive && $this->value) {
            try {
                $decrypted = Crypt::decryptString($this->value);
                return $this->castValue($decrypted);
            } catch (\Exception $e) {
                return $this->castValue($this->value);
            }
        }

        return $this->castValue($this->value);
    }

    /**
     * Set the value with encryption if sensitive
     */
    public function setValueAttribute($value)
    {
        if ($this->is_sensitive && $value) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Cast value to proper type
     */
    private function castValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'array':
                return is_string($value) ? explode(',', $value) : $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped()
    {
        try {
            return Cache::remember('broadcast_settings', 3600, function () {
                return self::where('is_active', true)
                    ->orderBy('group')
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->get()
                    ->groupBy('group');
            });
        } catch (\Exception $e) {
            // Return empty collection if table doesn't exist
            return collect();
        }
    }

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        try {
            $setting = self::where('key', $key)->where('is_active', true)->first();
            return $setting ? $setting->typed_value : $default;
        } catch (\Exception $e) {
            // Return default if table doesn't exist
            return $default;
        }
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->value = $value;
            $setting->save();

            // Clear cache when setting is updated
            Cache::forget('broadcast_settings');
            Cache::forget('broadcast_config');

            return true;
        }
        return false;
    }

    /**
     * Get broadcast configuration array
     */
    public static function getBroadcastConfig()
    {
        try {
            return Cache::remember('broadcast_config', 3600, function () {
                $settings = self::where('is_active', true)->get();
                $config = [];

                foreach ($settings as $setting) {
                    $config[$setting->key] = $setting->typed_value;
                }

                return $config;
            });
        } catch (\Exception $e) {
            // Return default config if table doesn't exist
            return [
                'broadcast_driver' => env('BROADCAST_DRIVER', 'pusher'),
                'broadcast_enabled' => true,
                'pusher_app_key' => env('PUSHER_APP_KEY', 'chatapp-key'),
                'pusher_host' => env('PUSHER_HOST', '127.0.0.1'),
                'pusher_port' => env('PUSHER_PORT', 8080),
                'websocket_host' => env('PUSHER_HOST', '127.0.0.1'),
                'websocket_port' => 6001,
            ];
        }
    }

    /**
     * Validate setting value (validation removed - all values accepted)
     */
    public function validateValue($value)
    {
        // Validation removed - accept all values including empty ones
        return ['success' => true];
    }

    /**
     * Scope for specific group
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get mobile app configuration
     */
    public static function getAppConfig()
    {
        try {
            $settings = self::where('is_active', true)->get()->keyBy('key');
            $broadcastType = $settings['pusher_service_type']->typed_value ?? 'reverb';
            $broadcastEnabled = filter_var($settings['broadcast_enabled']->typed_value ?? true, FILTER_VALIDATE_BOOLEAN);

            $config = [
                'broadcast_enabled' => $broadcastEnabled,
                'broadcast_type' => $broadcastType,
                'broadcast_service_type' => $broadcastType, // Add this for mobile app compatibility
                'app_name' => config('app.name', 'FarmersNetwork'),
                'app_logo' => asset('images/logo.png'),
                'walkthrough_message' => 'Welcome to FarmersNetwork - Connect with farmers worldwide!',
                'api_base_url' => url('/api'),
                'websocket_auth_endpoint' => url('/broadcasting/auth'),
            ];

            if ($broadcastEnabled) {
                if ($broadcastType === 'pusher_cloud') {
                    // Pusher Cloud configuration
                    $config = array_merge($config, [
                        'pusher_key' => $settings['pusher_cloud_app_key']->typed_value ?? '',
                        'pusher_cluster' => $settings['pusher_cloud_cluster']->typed_value ?? 'us2',
                        'pusher_use_tls' => filter_var($settings['pusher_cloud_use_tls']->typed_value ?? true, FILTER_VALIDATE_BOOLEAN),
                        'pusher_encrypted' => filter_var($settings['pusher_cloud_use_tls']->typed_value ?? true, FILTER_VALIDATE_BOOLEAN),

                        // Add all Pusher Cloud specific fields for mobile app
                        'pusher_service_type' => 'pusher_cloud',
                        'pusher_cloud_app_id' => $settings['pusher_cloud_app_id']->typed_value ?? '',
                        'pusher_cloud_app_key' => $settings['pusher_cloud_app_key']->typed_value ?? '',
                        'pusher_cloud_app_secret' => $settings['pusher_cloud_app_secret']->typed_value ?? '',
                        'pusher_cloud_cluster' => $settings['pusher_cloud_cluster']->typed_value ?? 'us2',
                        'pusher_cloud_use_tls' => filter_var($settings['pusher_cloud_use_tls']->typed_value ?? true, FILTER_VALIDATE_BOOLEAN),
                    ]);
                } else {
                    // Laravel Reverb configuration
                    $config = array_merge($config, [
                        'pusher_key' => $settings['pusher_app_key']->typed_value ?? 'chatapp-key',
                        'reverb_host' => $settings['pusher_host']->typed_value ?? '127.0.0.1',
                        'reverb_port' => (int)($settings['pusher_port']->typed_value ?? 8080),
                        'websocket_host' => $settings['websocket_host']->typed_value ?? '127.0.0.1',
                        'websocket_port' => (int)($settings['websocket_port']->typed_value ?? 6001),
                        'websocket_scheme' => $settings['pusher_scheme']->typed_value ?? 'http',
                        'pusher_use_tls' => ($settings['pusher_scheme']->typed_value ?? 'http') === 'https',
                        'pusher_encrypted' => ($settings['pusher_scheme']->typed_value ?? 'http') === 'https',

                        // Add service type for mobile app
                        'pusher_service_type' => 'reverb',
                    ]);
                }
            }

            return $config;

        } catch (\Exception $e) {
            // Return default config if table doesn't exist
            return [
                'broadcast_enabled' => false,
                'broadcast_type' => 'reverb',
                'app_name' => config('app.name', 'FarmersNetwork'),
                'app_logo' => asset('images/logo.png'),
                'walkthrough_message' => 'Welcome to FarmersNetwork!',
                'api_base_url' => url('/api'),
                'websocket_auth_endpoint' => url('/broadcasting/auth'),
                'error' => 'Configuration not available'
            ];
        }
    }
}
