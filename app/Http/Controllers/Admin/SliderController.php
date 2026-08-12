<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Slider;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin CRUD for the Features-screen slider.
 *
 * Images go through MediaStorageService, so they land on Cloudinary when it's
 * configured and fall back to local storage when it isn't — same path as radio
 * thumbnails.
 */
class SliderController extends Controller
{
    public function __construct(private MediaStorageService $media)
    {
    }

    public function index(): View
    {
        return view('admin.sliders.index', [
            'sliders' => Slider::ordered()->get(),
            'stats' => [
                'total' => Slider::count(),
                'active' => Slider::active()->count(),
                'hidden' => Slider::where('is_active', false)->count(),
            ],
            'config' => [
                'height' => Setting::get('slider_height') ?: Slider::DEFAULT_HEIGHT,
                'autoplay_seconds' => Setting::get('slider_autoplay_seconds') ?: 4,
                'enabled' => Setting::get('sliders_enabled'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.sliders.create', [
            'slider' => new Slider(['image_fit' => 'cover', 'is_active' => true]),
            'fits' => Slider::FITS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, isUpdate: false);

        $slider = new Slider();
        $slider->sort_order = $data['sort_order'] ?? Slider::nextSortOrder();

        $error = $this->fill($slider, $request, $data);

        if ($error !== null) {
            return back()->withInput()->with('error', $error);
        }

        $slider->save();

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slide added.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.edit', [
            'slider' => $slider,
            'fits' => Slider::FITS,
        ]);
    }

    public function update(Request $request, Slider $slider): RedirectResponse
    {
        $data = $this->validated($request, isUpdate: true);

        $error = $this->fill($slider, $request, $data);

        if ($error !== null) {
            return back()->withInput()->with('error', $error);
        }

        $slider->save();

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slide updated.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        // Drop the stored image too, or deleted slides quietly accumulate
        // orphaned files on Cloudinary and on disk.
        $this->media->forget($slider->image_disk, $slider->image_public_id, $slider->image_path);

        $slider->delete();

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slide deleted.');
    }

    /** Show / hide a slide in the app without deleting it. */
    public function toggleActive(Slider $slider): RedirectResponse
    {
        $slider->update(['is_active' => !$slider->is_active]);

        return back()->with(
            'success',
            'Slide is now ' . ($slider->is_active ? 'visible' : 'hidden') . '.'
        );
    }

    /**
     * Persist a new order after a drag-and-drop on the index page.
     *
     * Takes the full list of ids in their new order and renumbers them, rather
     * than trying to compute a single moved item's position.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sliders,id',
        ]);

        foreach ($data['ids'] as $position => $id) {
            Slider::where('id', $id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function validated(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',

            // An image is required for a new slide — one without it would
            // render as an empty grey box — but on edit the existing image
            // stands when no new file is sent.
            'image' => ($isUpdate ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png,webp,gif|max:8192',
            'image_url_manual' => 'nullable|url|max:2048',

            'link_url' => 'nullable|url|max:2048',
            'link_label' => 'nullable|string|max:100',

            // Bounded: below ~80px the caption is unreadable, above ~500px the
            // slider swallows the whole screen.
            'height' => 'nullable|integer|min:80|max:500',
            'image_fit' => 'required|in:' . implode(',', array_keys(Slider::FITS)),

            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'image.required' => 'Please choose an image for this slide.',
            'image.max' => 'The image must be 8MB or smaller.',
            'height.min' => 'A slide shorter than 80px cannot show its caption.',
            'height.max' => 'A slide taller than 500px fills most of the screen.',
        ]);
    }

    /**
     * Copy validated input onto the model.
     *
     * Returns an error message when the image upload itself failed, so the
     * caller can send the admin back to the form instead of silently saving a
     * slide with no picture.
     */
    private function fill(Slider $slider, Request $request, array $data): ?string
    {
        $slider->fill([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'link_label' => $data['link_label'] ?? null,
            'height' => $data['height'] ?? null,
            'image_fit' => $data['image_fit'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (array_key_exists('sort_order', $data) && $data['sort_order'] !== null) {
            $slider->sort_order = $data['sort_order'];
        }

        if ($request->hasFile('image')) {
            $result = $this->media->putImage($request->file('image'), 'sliders');

            if (!($result['success'] ?? false)) {
                return 'The image could not be uploaded: ' . ($result['error'] ?? 'unknown error');
            }

            // Replace, then clean up what was there before.
            $this->media->forget($slider->image_disk, $slider->image_public_id, $slider->image_path);

            $slider->image_url = $result['url'];
            $slider->image_public_id = $result['public_id'];
            $slider->image_disk = $result['disk'];
            $slider->image_path = $result['path'];
        } elseif (filled($data['image_url_manual'] ?? null)) {
            // Pasting a URL is the escape hatch for images already hosted
            // elsewhere. It replaces any uploaded file, so the old one goes.
            $this->media->forget($slider->image_disk, $slider->image_public_id, $slider->image_path);

            $slider->image_url = $data['image_url_manual'];
            $slider->image_public_id = null;
            $slider->image_disk = null;
            $slider->image_path = null;
        }

        return null;
    }
}
