<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Display defaults for the Features-screen slider.
 *
 * These are global: individual slides can override the height, but autoplay
 * speed and corner radius apply to the carousel as a whole. All public — the
 * app needs them to lay the slider out before any slide loads.
 */
return new class extends Migration
{
    /** [key, type, group, label, description, default, options] */
    private array $rows = [
        [
            'sliders_enabled', 'boolean', 'sliders',
            'Show Slider',
            'Turn the whole slider off without deleting any slides.',
            '1', null,
        ],
        [
            'slider_height', 'integer', 'sliders',
            'Default Slide Height (px)',
            'Used by any slide that does not set its own height. 120–400 is sensible.',
            '180', null,
        ],
        [
            'slider_image_fit', 'string', 'sliders',
            'Default Image Fit',
            'How images fill the slide when the slide does not specify its own.',
            'cover', ['cover', 'contain', 'fill'],
        ],
        [
            'slider_autoplay', 'boolean', 'sliders',
            'Auto-advance Slides',
            'Move to the next slide automatically.',
            '1', null,
        ],
        [
            'slider_autoplay_seconds', 'integer', 'sliders',
            'Seconds Per Slide',
            'How long each slide stays on screen before advancing.',
            '4', null,
        ],
        [
            'slider_corner_radius', 'integer', 'sliders',
            'Corner Radius (px)',
            'Roundness of the slide corners. 0 for square.',
            '16', null,
        ],
        [
            'slider_show_dots', 'boolean', 'sliders',
            'Show Position Dots',
            'The row of dots underneath showing which slide you are on.',
            '1', null,
        ],
    ];

    public function up(): void
    {
        foreach ($this->rows as [$key, $type, $group, $label, $description, $default, $options]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    // Never clobber a value an admin has already set.
                    'value' => Setting::where('key', $key)->value('value') ?: $default,
                    'type' => $type,
                    'group' => $group,
                    'label' => $label,
                    'description' => $description,
                    'options' => $options,
                    // The app reads these directly to lay out the carousel.
                    'is_public' => true,
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
