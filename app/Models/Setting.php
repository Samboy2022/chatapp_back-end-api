<?php

namespace App\Models;

use App\Helpers\ImageUrlHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'is_public'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "setting.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type, $setting->key);
        });
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group
            ]
        );

        // Clear cache
        Cache::forget("setting.{$key}");
        Cache::forget('settings.all');

        return $setting;
    }

    /**
     * Get all settings grouped by group
     */
    public static function getAllGrouped()
    {
        return Cache::remember('settings.all', 3600, function () {
            return self::all()->groupBy('group')->map(function ($settings) {
                return $settings->keyBy('key')->map(function ($setting) {
                    $setting->typed_value = self::castValue($setting->value, $setting->type, $setting->key);
                    return $setting;
                });
            });
        });
    }

    /**
     * Cast value to proper type
     */
    public static function castValue($value, $type, $key = null)
    {
        if (empty($value)) {
            return $value;
        }

        // Handle image URLs (specifically logo_url)
        if ($key === 'logo_url' || str_contains($key, 'image_url')) {
            return ImageUrlHelper::fullUrl($value);
        }

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Get typed value attribute
     */
    public function getTypedValueAttribute()
    {
        return self::castValue($this->value, $this->type, $this->key);
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
        Cache::forget('settings.all');
    }

    /**
     * Get all settings as key-value pairs for views
     */
    public static function getSettings()
    {
        return Cache::remember('settings.all.keyed', 3600, function () {
            return self::all()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            })->toArray();
        });
    }
}
