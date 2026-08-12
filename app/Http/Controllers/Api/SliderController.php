<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;

/**
 * Slides for the app's Features screen.
 *
 * Public on purpose: the slider is the first thing the screen paints and
 * carries no user data, so gating it behind auth would only delay the render.
 */
class SliderController extends Controller
{
    public function index(): JsonResponse
    {
        // A slide with no image would render as an empty grey box, so it never
        // reaches the app regardless of how it was saved.
        $sliders = Slider::query()
            ->active()
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->ordered()
            ->get()
            ->map(fn (Slider $slider) => $slider->toAppArray())
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'sliders' => $sliders,
                // Layout config travels with the slides so the app makes one
                // call instead of two and can never render slides with stale
                // sizing.
                'config' => $this->config(),
            ],
        ]);
    }

    /**
     * Carousel-wide display settings, with the same defaults the app uses if
     * it can't reach us at all.
     */
    private function config(): array
    {
        return [
            'enabled' => $this->boolSetting('sliders_enabled', true),
            'height' => (int) (Setting::get('slider_height') ?: Slider::DEFAULT_HEIGHT),
            'image_fit' => Setting::get('slider_image_fit') ?: 'cover',
            'autoplay' => $this->boolSetting('slider_autoplay', true),
            'autoplay_seconds' => max(1, (int) (Setting::get('slider_autoplay_seconds') ?: 4)),
            'corner_radius' => (int) (Setting::get('slider_corner_radius') ?? 16),
            'show_dots' => $this->boolSetting('slider_show_dots', true),
        ];
    }

    /** A missing setting means "not configured yet", not "off". */
    private function boolSetting(string $key, bool $default): bool
    {
        $value = Setting::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
