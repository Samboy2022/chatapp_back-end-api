<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Adds the settings the mobile app reads for branding (name, logo, colours,
 * walkthrough) and the group size cap.
 *
 * The admin settings screen renders whatever rows exist, grouped by `group`
 * and typed by `type` — so seeding rows here is all that's needed for them to
 * become editable in the dashboard.
 */
return new class extends Migration
{
    /**
     * [key, type, group, label, description, default]
     */
    private function definitions(): array
    {
        return [
            // ── Branding ────────────────────────────────────────────────────
            ['app_logo_url', 'image', 'branding', 'App Logo',
                'Shown on the splash, onboarding and chat headers in the mobile app.', ''],
            ['app_icon_url', 'image', 'branding', 'App Icon',
                'Small square mark used where the full logo does not fit.', ''],
            ['app_tagline', 'string', 'branding', 'App Tagline',
                'One-line strapline shown under the app name.', 'Promoting farmers skills on modern agriculture'],

            // Mobile theme colours. The `colors` group already holds the web
            // dashboard palette; these drive the Flutter theme specifically.
            ['mobile_color_primary', 'color', 'branding', 'Primary Colour',
                'Main brand colour: app bars, buttons, active icons.', '#128C7E'],
            ['mobile_color_secondary', 'color', 'branding', 'Secondary Colour',
                'Accent used for highlights and floating actions.', '#25D366'],
            ['mobile_color_accent', 'color', 'branding', 'Accent Colour',
                'Links and informational highlights.', '#34B7F1'],

            // ── Walkthrough / onboarding ────────────────────────────────────
            ['onboarding_1_image', 'image', 'onboarding', 'Slide 1 Image', 'Walkthrough screen 1 artwork.', ''],
            ['onboarding_1_title', 'string', 'onboarding', 'Slide 1 Title', '', 'Welcome to Farmers Network'],
            ['onboarding_1_description', 'text', 'onboarding', 'Slide 1 Description', '',
                'Connect with friends and family through secure messaging, voice calls, and video chats.'],

            ['onboarding_2_image', 'image', 'onboarding', 'Slide 2 Image', 'Walkthrough screen 2 artwork.', ''],
            ['onboarding_2_title', 'string', 'onboarding', 'Slide 2 Title', '', 'Stay Connected'],
            ['onboarding_2_description', 'text', 'onboarding', 'Slide 2 Description', '',
                'Share photos, videos, documents and your current location with end-to-end encryption.'],

            ['onboarding_3_image', 'image', 'onboarding', 'Slide 3 Image', 'Walkthrough screen 3 artwork.', ''],
            ['onboarding_3_title', 'string', 'onboarding', 'Slide 3 Title', '', 'Group Conversations'],
            ['onboarding_3_description', 'text', 'onboarding', 'Slide 3 Description', '',
                'Create groups to chat with multiple people and stay connected with communities.'],

            ['onboarding_4_image', 'image', 'onboarding', 'Slide 4 Image', 'Walkthrough screen 4 artwork.', ''],
            ['onboarding_4_title', 'string', 'onboarding', 'Slide 4 Title', '', 'Voice & Video Calls'],
            ['onboarding_4_description', 'text', 'onboarding', 'Slide 4 Description', '',
                'Make crystal clear voice and video calls to anyone, anywhere in the world.'],

            // ── Chat limits ─────────────────────────────────────────────────
            ['max_group_size', 'integer', 'chat', 'Maximum Group Members',
                'Largest number of people allowed in a single group, including the creator.', '256'],
        ];
    }

    public function up(): void
    {
        foreach ($this->definitions() as [$key, $type, $group, $label, $description, $default]) {
            // updateOrCreate so re-running never clobbers a value an admin has
            // already set — only the metadata is refreshed.
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => Setting::where('key', $key)->value('value') ?? $default,
                    'type' => $type,
                    'group' => $group,
                    'label' => $label,
                    'description' => $description,
                    'is_public' => true,
                ]
            );
        }

        Cache::flush();
    }

    public function down(): void
    {
        Setting::whereIn('key', array_column($this->definitions(), 0))->delete();
        Cache::flush();
    }
};
