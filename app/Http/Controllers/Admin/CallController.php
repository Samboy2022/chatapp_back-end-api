<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use App\Models\Chat;
use App\Models\RealtimeSetting;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    /**
     * Display a listing of calls
     */
    public function index(Request $request)
    {
        $query = Call::with(['caller', 'receiver', 'chat', 'participants.user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('caller', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('chat', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by call type
        if ($request->filled('call_type')) {
            $query->where('call_type', $request->call_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by caller
        if ($request->filled('caller_id')) {
            $query->where('caller_id', $request->caller_id);
        }

        // Filter by chat
        if ($request->filled('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }

        // Filter by duration range
        if ($request->filled('min_duration')) {
            $query->where('duration', '>=', $request->min_duration);
        }
        if ($request->filled('max_duration')) {
            $query->where('duration', '<=', $request->max_duration);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'started_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $calls = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $chats = Chat::select('id', 'name', 'type')->orderBy('name')->get();

        return view('admin.calls.index', compact('calls', 'users', 'chats'));
    }

    /**
     * Show the form for creating a new call record
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $chats = Chat::with('participants')->orderBy('name')->get();
        return view('admin.calls.create', compact('users', 'chats'));
    }

    /**
     * Store a newly created call record
     */
    public function store(Request $request)
    {
        $request->validate([
            'caller_id' => 'required|exists:users,id',
            'chat_id' => 'required|exists:chats,id',
            'call_type' => 'required|in:voice,video',
            'status' => 'required|in:initiated,ringing,answered,declined,missed,ended,failed',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'duration' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'caller_id', 'chat_id', 'call_type', 'status', 'started_at', 'ended_at', 'duration'
        ]);

        // Calculate duration if not provided
        if (!$data['duration'] && $data['ended_at']) {
            $startTime = \Carbon\Carbon::parse($data['started_at']);
            $endTime = \Carbon\Carbon::parse($data['ended_at']);
            $data['duration'] = $endTime->diffInSeconds($startTime);
        }

        $call = Call::create($data);

        // Add call participants (all chat participants except caller)
        $chat = Chat::with('participants')->find($request->chat_id);
        foreach ($chat->participants as $participant) {
            if ($participant->id !== $request->caller_id) {
                $call->participants()->create([
                    'user_id' => $participant->id,
                    'joined_at' => $data['started_at'],
                    'left_at' => $data['ended_at'],
                    'status' => in_array($data['status'], ['answered', 'ended']) ? 'answered' : 'missed',
                ]);
            }
        }

        return redirect()->route('admin.calls.index')
                        ->with('success', 'Call record created successfully!');
    }

    /**
     * Display the specified call
     */
    public function show(Call $call)
    {
        $call->load(['caller', 'chat.participants', 'participants.user']);
        
        // Call statistics
        $stats = [
            'total_participants' => $call->participants->count() + 1, // +1 for caller
            'answered_participants' => $call->participants()->where('status', 'answered')->count(),
            'missed_participants' => $call->participants()->where('status', 'missed')->count(),
            'duration_formatted' => $this->formatDuration($call->duration),
            'quality_score' => $call->quality_score ?: 'N/A',
            'call_rating' => $call->call_rating ?: 'Not Rated',
        ];

        return view('admin.calls.show', compact('call', 'stats'));
    }

    /**
     * Show the form for editing the specified call
     */
    public function edit(Call $call)
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $chats = Chat::select('id', 'name', 'type')->orderBy('name')->get();
        return view('admin.calls.edit', compact('call', 'users', 'chats'));
    }

    /**
     * Update the specified call
     */
    public function update(Request $request, Call $call)
    {
        $request->validate([
            'status' => 'required|in:initiated,ringing,answered,declined,missed,ended,failed',
            'ended_at' => 'nullable|date|after:started_at',
            'duration' => 'nullable|integer|min:0',
            'quality_score' => 'nullable|numeric|min:1|max:5',
            'call_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $data = $request->only(['status', 'ended_at', 'duration', 'quality_score', 'call_rating']);

        // Recalculate duration if ended_at changed
        if ($request->filled('ended_at') && !$request->filled('duration')) {
            $startTime = \Carbon\Carbon::parse($call->started_at);
            $endTime = \Carbon\Carbon::parse($request->ended_at);
            $data['duration'] = $endTime->diffInSeconds($startTime);
        }

        $call->update($data);

        return redirect()->route('admin.calls.show', $call)
                        ->with('success', 'Call updated successfully!');
    }

    /**
     * Remove the specified call
     */
    public function destroy(Call $call)
    {
        $call->delete();

        return redirect()->route('admin.calls.index')
                        ->with('success', 'Call record deleted successfully!');
    }

    /**
     * Export calls data
     */
    public function export(Request $request)
    {
        $query = Call::with(['caller', 'chat']);

        // Apply same filters as index
        if ($request->filled('call_type')) {
            $query->where('call_type', $request->call_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('caller_id')) {
            $query->where('caller_id', $request->caller_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $calls = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="calls_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function() use ($calls) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Caller', 'Chat', 'Type', 'Status', 'Duration (seconds)', 
                'Started At', 'Ended At', 'Quality Score'
            ]);

            foreach ($calls as $call) {
                fputcsv($file, [
                    $call->id,
                    $call->caller->name,
                    $call->chat->name ?? 'Private Chat',
                    ucfirst($call->call_type),
                    ucfirst($call->status),
                    $call->duration ?? 0,
                    $call->started_at ? $call->started_at->format('Y-m-d H:i:s') : '',
                    $call->ended_at ? $call->ended_at->format('Y-m-d H:i:s') : '',
                    $call->quality_score ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get call statistics
     */
    public function statistics()
    {
        $stats = [
            'total_calls' => Call::count(),
            'calls_today' => Call::whereDate('started_at', today())->count(),
            'voice_calls' => Call::where('call_type', 'voice')->count(),
            'video_calls' => Call::where('call_type', 'video')->count(),
            'answered_calls' => Call::where('status', 'answered')->count(),
            'missed_calls' => Call::where('status', 'missed')->count(),
            'declined_calls' => Call::where('status', 'declined')->count(),
            'failed_calls' => Call::where('status', 'failed')->count(),
            'average_duration' => Call::where('duration', '>', 0)->avg('duration'),
            'total_talk_time' => Call::sum('duration'),
            'active_callers' => User::whereHas('sentCalls', function($query) {
                $query->where('started_at', '>=', now()->subDays(7));
            })->count(),
            'peak_call_hour' => $this->getPeakCallHour(),
        ];

        return response()->json($stats);
    }

    /**
     * Get analytics data for charts
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '7days');
        
        switch ($period) {
            case '24hours':
                $data = $this->getHourlyCallData();
                break;
            case '7days':
                $data = $this->getDailyCallData(7);
                break;
            case '30days':
                $data = $this->getDailyCallData(30);
                break;
            default:
                $data = $this->getDailyCallData(7);
        }

        return response()->json($data);
    }

    /**
     * End an active call (supports both web and API requests)
     */
    public function endCall(Call $call)
    {
        try {
            if (!in_array($call->status, ['initiated', 'ringing', 'answered'])) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Call cannot be ended in current status.',
                    ], 400);
                }
                return redirect()->back()->with('error', 'Call cannot be ended in current status.');
            }

            $endedAt = now();

            // Calculate duration based on call status
            $duration = null;
            if ($call->status === 'answered' && $call->answered_at) {
                $duration = $call->answered_at->diffInSeconds($endedAt);
            } elseif ($call->started_at) {
                $duration = $call->started_at->diffInSeconds($endedAt);
            }

            $call->update([
                'status' => 'ended',
                'ended_at' => $endedAt,
                'duration' => $duration,
                'ended_by_admin' => true, // Flag to indicate admin intervention
            ]);

            // Update all participants if they exist
            if ($call->participants()->exists()) {
                $call->participants()->update([
                    'left_at' => $endedAt,
                    'status' => 'ended',
                ]);
            }

            // Load relationships and broadcast call ended event
            $call->load(['caller', 'receiver']);
            if ($call->caller && $call->receiver) {
                broadcast(new CallEnded($call, $call->caller, $call->receiver));
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Call ended successfully by admin',
                    'data' => [
                        'call_id' => $call->id,
                        'status' => $call->status,
                        'ended_at' => $call->ended_at->toISOString(),
                        'duration' => $duration,
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Call ended successfully!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to end call: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to end call: ' . $e->getMessage());
        }
    }

    /**
     * Format duration in human readable format
     */
    private function formatDuration($seconds)
    {
        if (!$seconds) return '0 seconds';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($secs > 0 || empty($parts)) $parts[] = "{$secs}s";
        
        return implode(' ', $parts);
    }

    /**
     * Get peak call hour
     */
    private function getPeakCallHour()
    {
        $hourCounts = Call::selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
                         ->groupBy('hour')
                         ->orderBy('count', 'desc')
                         ->first();
        
        return $hourCounts ? $hourCounts->hour . ':00' : 'N/A';
    }

    /**
     * Get hourly call data for last 24 hours
     */
    private function getHourlyCallData()
    {
        $data = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $count = Call::whereBetween('started_at', [
                $hour->copy()->startOfHour(),
                $hour->copy()->endOfHour()
            ])->count();
            
            $data[] = [
                'time' => $hour->format('H:00'),
                'calls' => $count
            ];
        }
        
        return $data;
    }

    /**
     * Get daily call data
     */
    private function getDailyCallData($days)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Call::whereDate('started_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M j'),
                'calls' => $count
            ];
        }
        
        return $data;
    }

    /**
     * Get active calls for real-time monitoring
     *
     * Note: This endpoint is temporarily accessible without authentication for testing
     */
    public function activeCalls(): JsonResponse
    {
        // Temporarily disable CSRF protection for this endpoint during testing
        if (app()->environment('local')) {
            // Skip authentication check for local development
        }
        $activeCalls = Call::with(['caller:id,name,avatar_url,email', 'receiver:id,name,avatar_url,email'])
            ->whereIn('status', ['initiated', 'ringing', 'answered'])
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($call) {
                // Calculate current duration for active calls
                $duration = 0;
                if ($call->status === 'answered' && $call->answered_at) {
                    $duration = $call->answered_at->diffInSeconds(now());
                } elseif ($call->started_at) {
                    $duration = $call->started_at->diffInSeconds(now());
                }

                return [
                    'id' => $call->id,
                    'call_id' => $call->call_id ?? $call->id,
                    'caller' => [
                        'id' => $call->caller->id,
                        'name' => $call->caller->name,
                        'avatar' => $call->caller->avatar_url ?? '/default-avatar.png',
                        'email' => $call->caller->email,
                    ],
                    'receiver' => [
                        'id' => $call->receiver->id,
                        'name' => $call->receiver->name,
                        'avatar' => $call->receiver->avatar_url ?? '/default-avatar.png',
                        'email' => $call->receiver->email,
                    ],
                    'call_type' => $call->call_type,
                    'status' => $call->status,
                    'started_at' => $call->started_at->toISOString(),
                    'duration' => $duration,
                    'duration_formatted' => $this->formatDuration($duration),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activeCalls,
            'count' => $activeCalls->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get real-time call statistics
     *
     * Note: This endpoint is temporarily accessible without authentication for testing
     */
    public function realtimeStats(): JsonResponse
    {
        // Temporarily disable CSRF protection for this endpoint during testing
        if (app()->environment('local')) {
            // Skip authentication check for local development
        }
        // Calculate success rate for today
        $totalCallsToday = Call::whereDate('started_at', today())->count();
        $successRateToday = 0;

        if ($totalCallsToday > 0) {
            $successfulCallsToday = Call::whereDate('started_at', today())
                ->whereIn('status', ['answered', 'ended'])
                ->count();
            $successRateToday = round(($successfulCallsToday / $totalCallsToday) * 100, 1);
        }

        $stats = [
            'active_calls' => Call::whereIn('status', ['initiated', 'ringing', 'answered'])->count(),
            'calls_today' => $totalCallsToday,
            'calls_this_hour' => Call::whereBetween('started_at', [
                now()->startOfHour(),
                now()->endOfHour()
            ])->count(),
            'total_duration_today' => Call::whereDate('started_at', today())->sum('duration'),
            'average_call_duration_today' => Call::whereDate('started_at', today())
                ->where('duration', '>', 0)
                ->avg('duration'),
            'success_rate_today' => $successRateToday,
            'broadcast_status' => $this->getBroadcastStatus(),
        ];

        // Format durations
        $stats['total_duration_today_formatted'] = $this->formatDuration($stats['total_duration_today'] ?? 0);
        $stats['average_call_duration_today_formatted'] = $this->formatDuration($stats['average_call_duration_today'] ?? 0);
        $stats['success_rate_today'] = round($stats['success_rate_today'], 2);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }



    /**
     * Get recent call activity for real-time feed
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $since = $request->get('since'); // ISO timestamp

        $query = Call::with(['caller:id,name,avatar_url', 'receiver:id,name,avatar_url'])
            ->orderBy('started_at', 'desc');

        if ($since) {
            $query->where('started_at', '>', $since);
        }

        $recentCalls = $query->limit($limit)->get()->map(function ($call) {
            // Check if caller and receiver exist
            if (!$call->caller || !$call->receiver) {
                return null; // Skip this call if data is incomplete
            }

            return [
                'id' => $call->id,
                'call_id' => $call->call_id ?? $call->id,
                'caller' => [
                    'id' => $call->caller->id,
                    'name' => $call->caller->name,
                    'avatar' => $call->caller->avatar_url ?? '/default-avatar.png',
                ],
                'receiver' => [
                    'id' => $call->receiver->id,
                    'name' => $call->receiver->name,
                    'avatar' => $call->receiver->avatar_url ?? '/default-avatar.png',
                ],
                'call_type' => $call->call_type,
                'status' => $call->status,
                'started_at' => $call->started_at->toISOString(),
                'ended_at' => $call->ended_at?->toISOString(),
                'duration' => $call->duration,
                'duration_formatted' => $this->formatDuration($call->duration ?? 0),
                'time_ago' => $call->started_at->diffForHumans(),
            ];
        })->filter(); // Remove null entries

        return response()->json([
            'success' => true,
            'data' => $recentCalls,
            'count' => $recentCalls->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get broadcast status for admin monitoring
     */
    private function getBroadcastStatus(): array
    {
        try {
            // Try to get settings from RealtimeSetting model if it exists
            if (class_exists('\\App\\Models\\RealtimeSetting')) {
                try {
                    $settings = \App\Models\RealtimeSetting::current();
                    $testResult = \App\Models\RealtimeSetting::testConnection();

                    return [
                        'enabled' => $settings->status === 'enabled',
                        'driver' => $settings->driver,
                        'connected' => $testResult['success'],
                        'message' => $testResult['message'],
                    ];
                } catch (\Exception $e) {
                    // Continue to fallback
                }
            }

            // Fallback to BroadcastSetting model
            if (class_exists('\\App\\Models\\BroadcastSetting')) {
                $broadcastSettings = \App\Models\BroadcastSetting::first();

                if ($broadcastSettings) {
                    return [
                        'enabled' => $broadcastSettings->enabled,
                        'connected' => $broadcastSettings->enabled, // Simplified
                        'driver' => $broadcastSettings->driver,
                        'message' => 'Using BroadcastSetting model',
                    ];
                }
            }

            // Default fallback
            return [
                'enabled' => config('broadcasting.default') !== 'null',
                'connected' => config('broadcasting.default') !== 'null',
                'driver' => config('broadcasting.default', 'log'),
                'message' => 'Using config fallback',
            ];
        } catch (\Exception $e) {
            return [
                'enabled' => false,
                'driver' => 'log',
                'connected' => false,
                'message' => 'Failed to get broadcast status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate success rate for a given date
     */
    private function calculateSuccessRate($date): float
    {
        $totalCalls = Call::whereDate('started_at', $date)->count();

        if ($totalCalls === 0) {
            return 0;
        }

        $successfulCalls = Call::whereDate('started_at', $date)
            ->whereIn('status', ['answered', 'ended'])
            ->count();

        return ($successfulCalls / $totalCalls) * 100;
    }


}
