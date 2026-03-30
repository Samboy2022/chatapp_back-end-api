<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AppSettingsController extends Controller
{
    /**
     * Get all public settings
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $settings = Cache::remember('api.settings.public', 3600, function () {
                return Setting::where('is_public', true)
                    ->get()
                    ->groupBy('group')
                    ->map(function ($groupSettings) {
                        return $groupSettings->keyBy('key')->map(function ($setting) {
                            return [
                                'key' => $setting->key,
                                'value' => $setting->typed_value,
                                'type' => $setting->type,
                                'label' => $setting->label,
                                'description' => $setting->description,
                                'options' => $setting->options,
                                'updated_at' => $setting->updated_at->toISOString()
                            ];
                        });
                    });
            });

            return response()->json([
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $settings,
                'meta' => [
                    'total_groups' => $settings->count(),
                    'total_settings' => $settings->flatten(1)->count(),
                    'cache_expires_at' => now()->addHour()->toISOString(),
                    'version' => config('app.version', '1.0.0')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get settings by group
     * 
     * @param string $group
     * @return JsonResponse
     */
    public function getByGroup(string $group): JsonResponse
    {
        try {
            $settings = Cache::remember("api.settings.group.{$group}", 3600, function () use ($group) {
                return Setting::where('is_public', true)
                    ->where('group', $group)
                    ->get()
                    ->keyBy('key')
                    ->map(function ($setting) {
                        return [
                            'key' => $setting->key,
                            'value' => $setting->typed_value,
                            'type' => $setting->type,
                            'label' => $setting->label,
                            'description' => $setting->description,
                            'options' => $setting->options,
                            'updated_at' => $setting->updated_at->toISOString()
                        ];
                    });
            });

            if ($settings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "No public settings found for group '{$group}'",
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Settings for group '{$group}' retrieved successfully",
                'data' => $settings,
                'meta' => [
                    'group' => $group,
                    'total_settings' => $settings->count(),
                    'cache_expires_at' => now()->addHour()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve group settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific setting by key
     * 
     * @param string $key
     * @return JsonResponse
     */
    public function getByKey(string $key): JsonResponse
    {
        try {
            $setting = Cache::remember("api.setting.{$key}", 3600, function () use ($key) {
                return Setting::where('key', $key)
                    ->where('is_public', true)
                    ->first();
            });

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => "Setting '{$key}' not found or not public",
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Setting '{$key}' retrieved successfully",
                'data' => [
                    'key' => $setting->key,
                    'value' => $setting->typed_value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'options' => $setting->options,
                    'updated_at' => $setting->updated_at->toISOString()
                ],
                'meta' => [
                    'cache_expires_at' => now()->addHour()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get multiple settings by keys
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keys' => 'required|array|min:1|max:50',
            'keys.*' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $keys = $request->input('keys');
            $cacheKey = 'api.settings.multiple.' . md5(implode(',', $keys));
            
            $settings = Cache::remember($cacheKey, 3600, function () use ($keys) {
                return Setting::whereIn('key', $keys)
                    ->where('is_public', true)
                    ->get()
                    ->keyBy('key')
                    ->map(function ($setting) {
                        return [
                            'key' => $setting->key,
                            'value' => $setting->typed_value,
                            'type' => $setting->type,
                            'group' => $setting->group,
                            'label' => $setting->label,
                            'description' => $setting->description,
                            'options' => $setting->options,
                            'updated_at' => $setting->updated_at->toISOString()
                        ];
                    });
            });

            $foundKeys = $settings->keys()->toArray();
            $missingKeys = array_diff($keys, $foundKeys);

            return response()->json([
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $settings,
                'meta' => [
                    'requested_keys' => $keys,
                    'found_keys' => $foundKeys,
                    'missing_keys' => $missingKeys,
                    'total_found' => count($foundKeys),
                    'total_missing' => count($missingKeys),
                    'cache_expires_at' => now()->addHour()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available groups
     *
     * @return JsonResponse
     */
    public function getGroups(): JsonResponse
    {
        try {
            $groups = Cache::remember('api.settings.groups', 3600, function () {
                return Setting::where('is_public', true)
                    ->select('group')
                    ->distinct()
                    ->get()
                    ->pluck('group')
                    ->map(function ($group) {
                        $count = Setting::where('group', $group)
                            ->where('is_public', true)
                            ->count();

                        return [
                            'name' => $group,
                            'label' => ucfirst(str_replace('_', ' ', $group)),
                            'settings_count' => $count
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'message' => 'Setting groups retrieved successfully',
                'data' => $groups,
                'meta' => [
                    'total_groups' => $groups->count(),
                    'cache_expires_at' => now()->addHour()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve setting groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get settings version/hash for cache validation
     *
     * @return JsonResponse
     */
    public function getVersion(): JsonResponse
    {
        try {
            $version = Cache::remember('api.settings.version', 300, function () {
                $lastUpdated = Setting::where('is_public', true)
                    ->max('updated_at');

                return [
                    'version' => md5($lastUpdated ?? 'default'),
                    'last_updated' => $lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->toISOString() : null,
                    'timestamp' => now()->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Settings version retrieved successfully',
                'data' => $version
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings version',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get essential app configuration for startup
     *
     * @return JsonResponse
     */
    public function getAppConfig(): JsonResponse
    {
        try {
            $config = Cache::remember('api.app.config', 1800, function () {
                $essentialSettings = Setting::where('is_public', true)
                    ->whereIn('key', [
                        'app_name',
                        'app_description',
                        'app_url',
                        'timezone',
                        'date_format',
                        'time_format',
                        'enable_file_upload',
                        'max_file_size',
                        'allowed_file_types',
                        'enable_voice_messages',
                        'max_group_size',
                        'enable_message_encryption',
                        'enable_video_calls',
                        'enable_group_calls',
                        'enable_status_updates'
                    ])
                    ->get()
                    ->keyBy('key')
                    ->map(function ($setting) {
                        return $setting->typed_value;
                    });

                return [
                    'app' => [
                        'name' => $essentialSettings['app_name'] ?? 'FarmersNetwork',
                        'description' => $essentialSettings['app_description'] ?? '',
                        'url' => $essentialSettings['app_url'] ?? config('app.url'),
                        'version' => config('app.version', '1.0.0'),
                        'environment' => config('app.env')
                    ],
                    'localization' => [
                        'timezone' => $essentialSettings['timezone'] ?? 'UTC',
                        'date_format' => $essentialSettings['date_format'] ?? 'Y-m-d',
                        'time_format' => $essentialSettings['time_format'] ?? 'H:i:s'
                    ],
                    'features' => [
                        'file_upload' => $essentialSettings['enable_file_upload'] ?? true,
                        'voice_messages' => $essentialSettings['enable_voice_messages'] ?? true,
                        'video_calls' => $essentialSettings['enable_video_calls'] ?? true,
                        'group_calls' => $essentialSettings['enable_group_calls'] ?? true,
                        'status_updates' => $essentialSettings['enable_status_updates'] ?? true,
                        'message_encryption' => $essentialSettings['enable_message_encryption'] ?? true
                    ],
                    'limits' => [
                        'max_file_size_mb' => $essentialSettings['max_file_size'] ?? 10,
                        'max_group_size' => $essentialSettings['max_group_size'] ?? 256,
                        'allowed_file_types' => explode(',', $essentialSettings['allowed_file_types'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,txt,mp3,mp4,mov')
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'App configuration retrieved successfully',
                'data' => $config,
                'meta' => [
                    'cache_expires_at' => now()->addMinutes(30)->toISOString(),
                    'generated_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve app configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
