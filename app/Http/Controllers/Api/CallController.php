<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Contact;
use App\Models\Chat;
use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallEnded;
use App\Events\CallRejected;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CallController extends Controller
{
    /**
     * Get call history for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            $type = $request->get('type'); // audio, video, or null for all
            $userId = Auth::id();

            // Get calls where user is either caller or receiver
            $callsQuery = Call::where(function($query) use ($userId) {
                $query->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->with([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ])
            ->orderBy('created_at', 'desc');

            if ($type && in_array($type, ['audio', 'video'])) {
                $callsQuery->where('call_type', $type);
            }

            $calls = $callsQuery->paginate($perPage);

            // Format the call data for response
            $formattedCalls = $calls->through(function($call) use ($userId) {
                $isIncoming = $call->caller_id !== $userId;
                $otherUser = $isIncoming ? $call->caller : $call->receiver;

                return [
                    'id' => $call->id,
                    'caller_id' => $call->caller_id,
                    'callee_id' => $call->receiver_id,
                    'type' => $call->call_type,
                    'status' => $call->status,
                    'duration' => $call->duration,
                    'started_at' => $call->started_at,
                    'ended_at' => $call->ended_at,
                    'caller' => [
                        'id' => $call->caller->id,
                        'name' => $call->caller->name,
                        'avatar' => $call->caller->avatar_url,
                    ],
                    'callee' => [
                        'id' => $call->receiver->id,
                        'name' => $call->receiver->name,
                        'avatar' => $call->receiver->avatar_url,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedCalls,
                'message' => 'Call history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving call history: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Initiate a new call
     */
    public function initiate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|exists:users,id',
                'type' => 'required|string|in:voice,video,audio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $receiverId = $request->receiver_id;
            $callType = $request->type;

            // Check if calling self
            if ($receiverId == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot call yourself'
                ], 400);
            }

            // Check if receiver has blocked the caller
            $isBlocked = Contact::where('user_id', $receiverId)
                ->where('contact_user_id', Auth::id())
                ->where('is_blocked', true)
                ->exists();

            if ($isBlocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to place call'
                ], 403);
            }

            // Check if there's already an active call between these users
            $activeCall = Call::where(function($query) use ($receiverId) {
                $query->where(function($subQuery) use ($receiverId) {
                    $subQuery->where('caller_id', Auth::id())
                             ->where('receiver_id', $receiverId);
                })
                ->orWhere(function($subQuery) use ($receiverId) {
                    $subQuery->where('caller_id', $receiverId)
                             ->where('receiver_id', Auth::id());
                });
            })
            ->whereIn('status', ['ringing', 'answered'])
            ->first();

            if ($activeCall) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already an active call between these users'
                ], 409);
            }

            // Find or create a private chat between the users
            $chat = Chat::where('type', 'private')
                ->whereHas('participants', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->whereHas('participants', function ($query) use ($receiverId) {
                    $query->where('user_id', $receiverId);
                })
                ->first();

            if (!$chat) {
                // Create a new private chat
                $chat = Chat::create([
                    'type' => 'private',
                    'name' => null, // Private chats don't have names
                    'created_by' => Auth::id()
                ]);

                // Add participants
                $chat->participants()->attach([
                    Auth::id() => ['joined_at' => now()],
                    $receiverId => ['joined_at' => now()]
                ]);
            }

            // Create the call record
            $call = Call::create([
                'chat_id' => $chat->id,
                'caller_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'call_type' => $callType,
                'status' => 'ringing',
                'started_at' => now()
            ]);

            $call->load([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ]);

            // Get caller and recipient user objects
            $caller = $call->caller;
            $recipient = $call->receiver;

            // Broadcast call event to receiver
            broadcast(new CallInitiated($call, $caller, $recipient));

            return response()->json([
                'success' => true,
                'data' => $call,
                'message' => 'Call initiated successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error initiating call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Answer a call
     */
    public function answer($callId): JsonResponse
    {
        try {
            $call = Call::findOrFail($callId);

            // Check if user is the receiver
            if ($call->receiver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to answer this call'
                ], 403);
            }

            // Check if call is still ringing
            if ($call->status !== 'ringing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Call is no longer available to answer'
                ], 410);
            }

            // Update call status
            $call->update([
                'status' => 'answered',
                'answered_at' => now()
            ]);

            $call->load([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ]);

            // Get caller and recipient user objects
            $caller = $call->caller;
            $recipient = $call->receiver;

            // Broadcast call accepted event
            broadcast(new CallAccepted($call, $caller, $recipient));

            return response()->json([
                'success' => true,
                'data' => $call,
                'message' => 'Call answered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error answering call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End a call
     */
    public function end($callId): JsonResponse
    {
        try {
            $call = Call::findOrFail($callId);

            // Check if user is participant in the call
            if ($call->caller_id !== Auth::id() && $call->receiver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to end this call'
                ], 403);
            }

            // Check if call is active
            if (!in_array($call->status, ['ringing', 'answered'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call is not active'
                ], 410);
            }

            // Calculate duration if call was answered
            $duration = 0;
            if ($call->status === 'answered' && $call->answered_at) {
                $duration = $call->answered_at->diffInSeconds(now());
            }

            // Update call status
            $call->update([
                'status' => 'ended',
                'ended_at' => now(),
                'duration' => $duration
            ]);

            $call->load([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ]);

            // Get caller and recipient user objects
            $caller = $call->caller;
            $recipient = $call->receiver;

            // Broadcast call ended event
            broadcast(new CallEnded($call, $caller, $recipient));

            return response()->json([
                'success' => true,
                'data' => $call,
                'message' => 'Call ended successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error ending call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decline a call
     */
    public function decline($callId): JsonResponse
    {
        try {
            $call = Call::findOrFail($callId);

            // Check if user is the receiver
            if ($call->receiver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to decline this call'
                ], 403);
            }

            // Check if call is still ringing
            if ($call->status !== 'ringing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Call is no longer available to decline'
                ], 410);
            }

            // Update call status
            $call->update([
                'status' => 'declined',
                'ended_at' => now()
            ]);

            $call->load([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ]);

            // Get caller and recipient user objects
            $caller = $call->caller;
            $recipient = $call->receiver;

            // Broadcast call rejected event
            broadcast(new CallRejected($call, $caller, $recipient));

            return response()->json([
                'success' => true,
                'data' => $call,
                'message' => 'Call declined successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error declining call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific call
     */
    public function show($callId): JsonResponse
    {
        try {
            $call = Call::with([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ])->findOrFail($callId);

            // Check if user is participant in the call
            if ($call->caller_id !== Auth::id() && $call->receiver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this call'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $call,
                'message' => 'Call details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving call details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get call statistics for the user
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $stats = [
                'total_calls' => Call::where('caller_id', $userId)
                    ->orWhere('receiver_id', $userId)
                    ->count(),
                
                'outgoing_calls' => Call::where('caller_id', $userId)->count(),
                
                'incoming_calls' => Call::where('receiver_id', $userId)->count(),
                
                'answered_calls' => Call::where(function($query) use ($userId) {
                    $query->where('caller_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('status', 'answered')->count(),
                
                'missed_calls' => Call::where('receiver_id', $userId)
                    ->whereIn('status', ['declined', 'ended'])
                    ->where('duration', 0)
                    ->count(),
                
                'total_talk_time' => Call::where(function($query) use ($userId) {
                    $query->where('caller_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('status', 'ended')
                  ->sum('duration'),
                
                'video_calls' => Call::where(function($query) use ($userId) {
                    $query->where('caller_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('type', 'video')->count(),
                
                'audio_calls' => Call::where(function($query) use ($userId) {
                    $query->where('caller_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })->where('type', 'audio')->count()
            ];

            // Format total talk time
            $stats['total_talk_time_formatted'] = $this->formatDuration($stats['total_talk_time']);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Call statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving call statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active calls for the authenticated user
     */
    public function getActiveCalls(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $activeCalls = Call::where(function($query) use ($userId) {
                $query->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->whereIn('status', ['ringing', 'answered'])
            ->with([
                'caller:id,name,phone_number,avatar_url',
                'receiver:id,name,phone_number,avatar_url'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            // Format the active calls data
            $formattedCalls = $activeCalls->map(function($call) use ($userId) {
                $isIncoming = $call->caller_id !== $userId;
                $otherUser = $isIncoming ? $call->caller : $call->receiver;

                return [
                    'id' => $call->id,
                    'caller_id' => $call->caller_id,
                    'callee_id' => $call->receiver_id,
                    'type' => $call->type,
                    'status' => $call->status,
                    'started_at' => $call->started_at,
                    'answered_at' => $call->answered_at,
                    'caller' => [
                        'id' => $call->caller->id,
                        'name' => $call->caller->name,
                        'avatar' => $call->caller->avatar_url,
                    ],
                    'callee' => [
                        'id' => $call->receiver->id,
                        'name' => $call->receiver->name,
                        'avatar' => $call->receiver->avatar_url,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedCalls,
                'message' => 'Active calls retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving active calls: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get missed calls count
     */
    public function getMissedCallsCount(): JsonResponse
    {
        try {
            $missedCount = Call::where('receiver_id', Auth::id())
                ->whereIn('status', ['declined', 'ended'])
                ->where('duration', 0)
                ->where('created_at', '>', now()->subDays(7)) // Last 7 days
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['missed_calls_count' => $missedCount],
                'message' => 'Missed calls count retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving missed calls count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format duration in human readable format
     */
    private function formatDuration($seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . $remainingSeconds . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $remainingSeconds = $seconds % 60;
            return $hours . 'h ' . $minutes . 'm ' . $remainingSeconds . 's';
        }
    }

    /**
     * Auto-end calls that have been ringing for too long
     */
    public function cleanupStaleRingingCalls(): JsonResponse
    {
        try {
            $staleThreshold = now()->subMinutes(2); // 2 minutes ago
            
            $staleCalls = Call::where('status', 'ringing')
                ->where('started_at', '<', $staleThreshold)
                ->get();
            
            $endedCount = 0;
            foreach ($staleCalls as $call) {
                $call->update([
                    'status' => 'ended',
                    'ended_at' => now()
                ]);

                // Load relationships and broadcast call ended event
                $call->load(['caller', 'receiver']);
                $caller = $call->caller;
                $recipient = $call->receiver;

                broadcast(new CallEnded($call, $caller, $recipient));
                $endedCount++;
            }

            return response()->json([
                'success' => true,
                'data' => ['ended_calls_count' => $endedCount],
                'message' => 'Stale ringing calls cleaned up successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning up stale calls: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept a call (alias for answer)
     */
    public function accept($callId): JsonResponse
    {
        return $this->answer($callId);
    }

    /**
     * Reject a call (alias for decline)
     */
    public function reject($callId): JsonResponse
    {
        return $this->decline($callId);
    }
}
