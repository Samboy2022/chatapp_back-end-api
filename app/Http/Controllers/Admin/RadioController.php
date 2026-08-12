<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadioProgram;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin CRUD for radio programmes: the live station, recorded shows and the
 * archive of past conversations.
 */
class RadioController extends Controller
{
    public function __construct(private MediaStorageService $media)
    {
    }

    public function index(Request $request): View
    {
        $query = RadioProgram::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('host', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $programs = $query->ordered()->paginate(20)->withQueryString();

        return view('admin.radio.index', [
            'programs' => $programs,
            'live' => RadioProgram::currentLive(),
            'types' => RadioProgram::TYPES,
            'stats' => [
                'total' => RadioProgram::count(),
                'programs' => RadioProgram::where('type', RadioProgram::TYPE_PROGRAM)->count(),
                'archive' => RadioProgram::where('type', RadioProgram::TYPE_ARCHIVE)->count(),
                'plays' => (int) RadioProgram::sum('play_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.radio.create', ['types' => RadioProgram::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, isUpdate: false);

        $program = new RadioProgram();
        $this->fill($program, $request, $data);
        $program->save();

        if ($program->isLive()) {
            $program->makeSoleLive();
        }

        return redirect()
            ->route('admin.radio.index')
            ->with('success', "\"{$program->title}\" has been added.");
    }

    public function edit(RadioProgram $radio): View
    {
        return view('admin.radio.edit', [
            'program' => $radio,
            'types' => RadioProgram::TYPES,
        ]);
    }

    public function update(Request $request, RadioProgram $radio): RedirectResponse
    {
        $data = $this->validated($request, isUpdate: true);

        $this->fill($radio, $request, $data);
        $radio->save();

        if ($radio->isLive()) {
            $radio->makeSoleLive();
        }

        return redirect()
            ->route('admin.radio.index')
            ->with('success', "\"{$radio->title}\" has been updated.");
    }

    public function destroy(RadioProgram $radio): RedirectResponse
    {
        $title = $radio->title;

        $this->media->forget($radio->audio_disk, $radio->audio_public_id, $radio->audio_path, isAudio: true);
        $this->media->forget($radio->thumbnail_disk, $radio->thumbnail_public_id, $radio->thumbnail_path);

        $radio->delete();

        return redirect()
            ->route('admin.radio.index')
            ->with('success', "\"{$title}\" has been deleted.");
    }

    /** Show / hide a programme in the app without deleting it. */
    public function toggleActive(RadioProgram $radio): RedirectResponse
    {
        $radio->update(['is_active' => !$radio->is_active]);

        return back()->with(
            'success',
            "\"{$radio->title}\" is now " . ($radio->is_active ? 'visible' : 'hidden') . '.'
        );
    }

    /** Put this programme on air, demoting whatever was live before. */
    public function setLive(RadioProgram $radio): RedirectResponse
    {
        $radio->update(['type' => RadioProgram::TYPE_LIVE, 'is_active' => true]);
        $radio->makeSoleLive();

        return back()->with('success', "\"{$radio->title}\" is now the live station.");
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function validated(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'type' => 'required|in:' . implode(',', array_keys(RadioProgram::TYPES)),
            'host' => 'nullable|string|max:255',

            // A live station points at a stream URL; recorded programmes get a
            // file. Either can be supplied on update, since the existing value
            // stands when neither is sent.
            'stream_url' => 'nullable|url|max:2048',
            'audio_file' => ($isUpdate ? 'nullable' : 'nullable') . '|file|mimetypes:audio/mpeg,audio/mp3,audio/aac,audio/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/ogg,audio/webm|max:204800',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',

            'duration_seconds' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'is_downloadable' => 'nullable|boolean',
        ]);
    }

    private function fill(RadioProgram $program, Request $request, array $data): void
    {
        $program->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'host' => $data['host'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? $program->duration_seconds,
            'sort_order' => $data['sort_order'] ?? $program->sort_order ?? 0,
            'published_at' => $data['published_at'] ?? $program->published_at ?? now(),
            'is_active' => $request->boolean('is_active'),
            'is_downloadable' => $request->boolean('is_downloadable'),
        ]);

        // A stream URL and an uploaded file are mutually exclusive — whichever
        // the admin supplied last wins, and the previous asset is cleaned up.
        if ($request->hasFile('audio_file')) {
            $result = $this->media->putAudio($request->file('audio_file'));

            if ($result['success']) {
                $this->media->forget(
                    $program->audio_disk,
                    $program->audio_public_id,
                    $program->audio_path,
                    isAudio: true
                );

                $program->audio_url = $result['url'];
                $program->audio_public_id = $result['public_id'];
                $program->audio_disk = $result['disk'];
                $program->audio_path = $result['path'];
                $program->file_size = $result['bytes'];
            }
        } elseif (filled($data['stream_url'] ?? null)) {
            $program->audio_url = $data['stream_url'];
            $program->audio_public_id = null;
            $program->audio_disk = null;
            $program->audio_path = null;
        }

        if ($request->hasFile('thumbnail')) {
            $result = $this->media->putImage($request->file('thumbnail'));

            if ($result['success']) {
                $this->media->forget(
                    $program->thumbnail_disk,
                    $program->thumbnail_public_id,
                    $program->thumbnail_path
                );

                $program->thumbnail_url = $result['url'];
                $program->thumbnail_public_id = $result['public_id'];
                $program->thumbnail_disk = $result['disk'];
                $program->thumbnail_path = $result['path'];
            }
        }
    }
}
