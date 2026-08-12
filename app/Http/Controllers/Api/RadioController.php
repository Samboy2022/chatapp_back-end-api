<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RadioProgramResource;
use App\Http\Resources\TvChannelResource;
use App\Models\RadioProgram;
use App\Models\TvChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only radio + TV feed for the mobile app.
 *
 * Everything here is public content curated by an admin, so the responses
 * carry no user data and are safe to cache on the device.
 */
class RadioController extends Controller
{
    /**
     * Everything the Radio screen needs, in one round trip.
     *
     * The app opens this screen from a grid tap, so a single call beats three
     * sequential ones on a slow connection.
     */
    public function index(): JsonResponse
    {
        $live = RadioProgram::currentLive();

        $programs = RadioProgram::query()
            ->active()->published()
            ->ofType(RadioProgram::TYPE_PROGRAM)
            ->ordered()
            ->get();

        $archive = RadioProgram::query()
            ->active()->published()
            ->ofType(RadioProgram::TYPE_ARCHIVE)
            ->ordered()
            ->get();

        $channels = TvChannel::query()->active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'live' => $live ? new RadioProgramResource($live) : null,
                'programs' => RadioProgramResource::collection($programs),
                'archive' => RadioProgramResource::collection($archive),
                'tv_channels' => TvChannelResource::collection($channels),
            ],
            'message' => 'Radio content retrieved successfully',
        ]);
    }

    /**
     * The on-air broadcast on its own — cheap enough to poll.
     */
    public function live(): JsonResponse
    {
        $live = RadioProgram::currentLive();

        return response()->json([
            'success' => true,
            'data' => $live ? new RadioProgramResource($live) : null,
            'message' => $live ? 'Live station retrieved' : 'No station is currently on air',
        ]);
    }

    /**
     * Programmes, optionally filtered by type (`program` or `archive`).
     */
    public function programs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|in:live,program,archive',
        ]);

        $query = RadioProgram::query()->active()->published()->ordered();

        if (!empty($validated['type'])) {
            $query->ofType($validated['type']);
        }

        return response()->json([
            'success' => true,
            'data' => RadioProgramResource::collection($query->get()),
            'message' => 'Radio programmes retrieved successfully',
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $program = RadioProgram::query()->active()->published()->find($id);

        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Programme not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RadioProgramResource($program),
            'message' => 'Programme retrieved successfully',
        ]);
    }

    /**
     * Record a play. Fire-and-forget from the client's perspective — a failure
     * here must never stop playback, so it always answers 200.
     */
    public function play(string $id): JsonResponse
    {
        RadioProgram::query()->find($id)?->incrementPlayCount();

        return response()->json(['success' => true, 'message' => 'Play recorded']);
    }

    public function download(string $id): JsonResponse
    {
        $program = RadioProgram::query()->active()->find($id);

        if (!$program || !$program->isDownloadable()) {
            return response()->json([
                'success' => false,
                'message' => 'This programme is not available for download',
            ], 404);
        }

        $program->incrementDownloadCount();

        return response()->json([
            'success' => true,
            'data' => ['audio_url' => $program->audio_url],
            'message' => 'Download URL retrieved',
        ]);
    }

    public function tvChannels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TvChannelResource::collection(
                TvChannel::query()->active()->ordered()->get()
            ),
            'message' => 'TV channels retrieved successfully',
        ]);
    }
}
