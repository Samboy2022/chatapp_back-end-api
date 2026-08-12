<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvChannel;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin CRUD for the TV tab of the app's Radio screen.
 */
class TvChannelController extends Controller
{
    public function __construct(private MediaStorageService $media)
    {
    }

    public function index(Request $request): View
    {
        $query = TvChannel::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return view('admin.radio.tv.index', [
            'channels' => $query->ordered()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.radio.tv.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $channel = new TvChannel();
        $this->fill($channel, $request, $data);
        $channel->save();

        return redirect()
            ->route('admin.tv-channels.index')
            ->with('success', "\"{$channel->title}\" has been added.");
    }

    public function edit(TvChannel $tvChannel): View
    {
        return view('admin.radio.tv.edit', ['channel' => $tvChannel]);
    }

    public function update(Request $request, TvChannel $tvChannel): RedirectResponse
    {
        $data = $this->validated($request);

        $this->fill($tvChannel, $request, $data);
        $tvChannel->save();

        return redirect()
            ->route('admin.tv-channels.index')
            ->with('success', "\"{$tvChannel->title}\" has been updated.");
    }

    public function destroy(TvChannel $tvChannel): RedirectResponse
    {
        $title = $tvChannel->title;

        $this->media->forget(
            $tvChannel->thumbnail_disk,
            $tvChannel->thumbnail_public_id,
            $tvChannel->thumbnail_path
        );

        $tvChannel->delete();

        return redirect()
            ->route('admin.tv-channels.index')
            ->with('success', "\"{$title}\" has been deleted.");
    }

    public function toggleActive(TvChannel $tvChannel): RedirectResponse
    {
        $tvChannel->update(['is_active' => !$tvChannel->is_active]);

        return back()->with(
            'success',
            "\"{$tvChannel->title}\" is now " . ($tvChannel->is_active ? 'visible' : 'hidden') . '.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'stream_url' => 'required|url|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'is_live' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function fill(TvChannel $channel, Request $request, array $data): void
    {
        $channel->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'stream_url' => $data['stream_url'],
            'sort_order' => $data['sort_order'] ?? $channel->sort_order ?? 0,
            'is_live' => $request->boolean('is_live'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('thumbnail')) {
            $result = $this->media->putImage($request->file('thumbnail'), 'radio/tv');

            if ($result['success']) {
                $this->media->forget(
                    $channel->thumbnail_disk,
                    $channel->thumbnail_public_id,
                    $channel->thumbnail_path
                );

                $channel->thumbnail_url = $result['url'];
                $channel->thumbnail_public_id = $result['public_id'];
                $channel->thumbnail_disk = $result['disk'];
                $channel->thumbnail_path = $result['path'];
            }
        }
    }
}
