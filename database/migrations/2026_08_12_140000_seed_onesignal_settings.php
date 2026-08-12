<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * OneSignal credentials, editable from the admin dashboard.
 *
 * Kept out of `is_public` — the REST API key can send notifications to every
 * user of the app, so it must never reach the mobile client.
 */
return new class extends Migration
{
    private array $rows = [
        [
            'onesignal_app_id',
            'string',
            'integrations',
            'OneSignal App ID',
            'From OneSignal → Settings → Keys & IDs.',
            '503fb0a4-c008-41b3-ba21-97433f1bdd45',
        ],
        [
            'onesignal_rest_api_key',
            'string',
            'integrations',
            'OneSignal REST API Key',
            'Server-side key used to send notifications. Never exposed to the app.',
            '',
        ],
    ];

    public function up(): void
    {
        foreach ($this->rows as [$key, $type, $group, $label, $description, $default]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    // Don't clobber a key an admin has already pasted in.
                    'value' => Setting::where('key', $key)->value('value') ?: $default,
                    'type' => $type,
                    'group' => $group,
                    'label' => $label,
                    'description' => $description,
                    'is_public' => false,
                ]
            );
        }

        Cache::flush();
    }

    public function down(): void
    {
        Setting::whereIn('key', array_column($this->rows, 0))->delete();
        Cache::flush();
    }
};
