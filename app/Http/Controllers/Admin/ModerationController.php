<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Moderation queue: user-submitted reports and the blocks that go with them.
 *
 * Kept separate from Admin\ReportController, which is analytics reporting and
 * has nothing to do with abuse handling despite the shared word.
 */
class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Report::with([
            'reporter:id,name,email,avatar_url',
            'reportedUser:id,name,email,avatar_url,is_blocked',
            'reviewer:id,name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                  ->orWhereHas('reportedUser', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('reporter', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        return view('admin.moderation.index', [
            'reports' => $reports,
            'statuses' => Report::STATUSES,
            'reasons' => Report::REASONS,
            'stats' => [
                'open' => Report::open()->count(),
                'pending' => Report::withStatus(Report::STATUS_PENDING)->count(),
                'resolved' => Report::withStatus(Report::STATUS_RESOLVED)->count(),
                // How many block relationships exist right now, across all users.
                'blocks' => Contact::where('is_blocked', true)->count(),
            ],
        ]);
    }

    public function show(Report $report): View
    {
        $report->load([
            'reporter:id,name,email,phone_number,avatar_url',
            'reportedUser:id,name,email,phone_number,avatar_url,is_blocked,created_at',
            'message:id,content,message_type,created_at',
            'reviewer:id,name',
        ]);

        return view('admin.moderation.show', [
            'report' => $report,
            'statuses' => Report::STATUSES,
            // Everything else filed against the same person — a single report
            // is weak signal, a pattern is not.
            'otherReports' => Report::where('reported_user_id', $report->reported_user_id)
                ->where('id', '!=', $report->id)
                ->with('reporter:id,name')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Report::STATUSES)),
            'moderator_notes' => 'nullable|string|max:2000',
        ]);

        $report->update([
            'status' => $data['status'],
            'moderator_notes' => $data['moderator_notes'] ?? $report->moderator_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report marked as ' . Report::STATUSES[$data['status']] . '.');
    }

    /** Suspend or restore the reported account. */
    public function toggleUserBlock(User $user): RedirectResponse
    {
        $user->update(['is_blocked' => !$user->is_blocked]);

        return back()->with(
            'success',
            "{$user->name} has been " . ($user->is_blocked ? 'suspended' : 'restored') . '.'
        );
    }

    /** Every block relationship in the system, for the Blocks tab. */
    public function blocks(Request $request): View
    {
        $query = Contact::where('is_blocked', true)
            ->with(['user:id,name,email,avatar_url', 'contactUser:id,name,email,avatar_url']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('contactUser', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        return view('admin.moderation.blocks', [
            'blocks' => $query->latest('updated_at')->paginate(25)->withQueryString(),
        ]);
    }
}
