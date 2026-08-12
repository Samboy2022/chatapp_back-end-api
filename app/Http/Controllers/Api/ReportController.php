<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Reporting users and messages from the mobile app.
 */
class ReportController extends Controller
{
    /** The reason list the app renders, so labels stay in one place. */
    public function reasons(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => collect(Report::REASONS)
                ->map(fn ($label, $key) => ['value' => $key, 'label' => $label])
                ->values(),
            'message' => 'Report reasons retrieved',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|in:' . implode(',', array_keys(Report::REASONS)),
            'details' => 'nullable|string|max:2000',
            'message_id' => 'nullable|exists:messages,id',
            'chat_id' => 'nullable|exists:chats,id',
            'block_user' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose a reason for the report',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ((int) $request->reported_user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot report yourself',
            ], 422);
        }

        $shouldBlock = $request->boolean('block_user');

        // Re-reporting the same person updates the open report rather than
        // filling the moderation queue with duplicates.
        $report = Report::updateOrCreate(
            [
                'reporter_id' => Auth::id(),
                'reported_user_id' => $request->reported_user_id,
                'status' => Report::STATUS_PENDING,
            ],
            [
                'reason' => $request->reason,
                'details' => $request->details,
                'message_id' => $request->message_id,
                'chat_id' => $request->chat_id,
                'blocked_by_reporter' => $shouldBlock,
            ]
        );

        if ($shouldBlock) {
            Contact::updateOrCreate(
                ['user_id' => Auth::id(), 'contact_user_id' => $request->reported_user_id],
                ['is_blocked' => true]
            );
        }

        return response()->json([
            'success' => true,
            'data' => ['report_id' => $report->id, 'blocked' => $shouldBlock],
            'message' => $shouldBlock
                ? 'Report submitted and user blocked'
                : 'Report submitted. Our team will review it.',
        ], 201);
    }

    /** Reports the signed-in user has filed, so the app can show history. */
    public function index(): JsonResponse
    {
        $reports = Report::where('reporter_id', Auth::id())
            ->with('reportedUser:id,name,avatar_url')
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => 'Reports retrieved',
        ]);
    }
}
