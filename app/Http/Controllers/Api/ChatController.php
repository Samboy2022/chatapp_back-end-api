<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get user's chats
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $chats = $user->chats()
                ->with([
                    'latestMessage.sender:id,name,avatar_url',
                    'participants:id,name,avatar_url,is_online,last_seen_at',
                    'creator:id,name,avatar_url'
                ])
                ->withPivot(['is_archived', 'is_pinned', 'muted_until', 'last_read_message_id'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($chat) use ($user) {
                    $chat->unread_count = $chat->getUnreadCount($user);
                    
                    // Fix: Properly handle muted_until from pivot data
                    $chat->is_muted = false;
                    if ($chat->pivot->muted_until) {
                        try {
                            $mutedUntil = \Carbon\Carbon::parse($chat->pivot->muted_until);
                            $chat->is_muted = $mutedUntil->isFuture();
                        } catch (\Exception $e) {
                            // If parsing fails, assume not muted
                            $chat->is_muted = false;
                        }
                    }
                    
                    // For private chats, get the other participant
                    if ($chat->isPrivate()) {
                        $chat->other_participant = $chat->getOtherParticipant($user);
                    }
                    
                    return $chat;
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'chats' => $chats
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or get existing chat with user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id',
            'type' => 'sometimes|in:private,group',
            'name' => 'required_if:type,group|string|max:255',
            'description' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUser = $request->user();
            $participantIds = $request->participants;

            // Add current user to participants if not already present
            if (!in_array($currentUser->id, $participantIds)) {
                $participantIds[] = $currentUser->id;
            }

            // Remove duplicates
            $participantIds = array_unique($participantIds);

            if (count($participantIds) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'A chat requires at least two participants.'
                ], 422);
            }

            $type = $request->get('type', count($participantIds) > 2 ? 'group' : 'private');

            if ($type === 'private') {
                $otherUser = User::findOrFail($participantIds[0] == $currentUser->id ? $participantIds[1] : $participantIds[0]);
                // Check if users have blocked each other
                if ($currentUser->hasBlocked($otherUser) || $otherUser->hasBlocked($currentUser)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot create chat with blocked user'
                    ], 403);
                }
                $chat = Chat::getOrCreatePrivateChat($currentUser, $otherUser);
            } else {
                // Create group chat
                $chat = Chat::create([
                    'type' => 'group',
                    'name' => $request->name,
                    'description' => $request->description,
                    'created_by' => $currentUser->id,
                ]);

                foreach($participantIds as $participantId) {
                    $role = ($participantId == $currentUser->id) ? 'admin' : 'member';
                    $chat->addParticipant(User::find($participantId), $role);
                }
            }

            $chat->load([
                'participants:id,name,avatar_url,is_online,last_seen_at',
                'latestMessage.sender:id,name,avatar_url'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat created successfully',
                'data' => [
                    'chat' => $chat
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific chat
     */
    public function show(Request $request, $chatId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $chat = Chat::with([
                'participants:id,name,avatar_url,is_online,last_seen_at',
                'creator:id,name,avatar_url'
            ])->findOrFail($chatId);

            // Check if user is participant
            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            $chat->unread_count = $chat->getUnreadCount($user);
            
            // For private chats, get the other participant
            if ($chat->isPrivate()) {
                $chat->other_participant = $chat->getOtherParticipant($user);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => $chat
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update chat (for group chats)
     */
    public function update(Request $request, $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
            'avatar_url' => 'sometimes|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $chat = Chat::findOrFail($chatId);

            // Check if user is participant and admin for group chats
            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            if ($chat->isGroup()) {
                $participant = $chat->participants()->where('users.id', $user->id)->first();
                if ($participant->pivot->role !== 'admin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only admins can update group chat details'
                    ], 403);
                }
            }

            $chat->update($request->only(['name', 'description', 'avatar_url']));

            return response()->json([
                'success' => true,
                'message' => 'Chat updated successfully',
                'data' => [
                    'chat' => $chat
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive/unarchive chat
     */
    public function archive(Request $request, $chatId): JsonResponse
    {
        try {
            $user = $request->user();
            $chat = Chat::findOrFail($chatId);

            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            $participant = $chat->participants()->where('users.id', $user->id)->first();
            $isArchived = !$participant->pivot->is_archived;

            $chat->participants()->updateExistingPivot($user->id, [
                'is_archived' => $isArchived
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat ' . ($isArchived ? 'archived' : 'unarchived') . ' successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pin/unpin chat
     */
    public function pin(Request $request, $chatId): JsonResponse
    {
        try {
            $user = $request->user();
            $chat = Chat::findOrFail($chatId);

            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            $participant = $chat->participants()->where('users.id', $user->id)->first();
            $isPinned = !$participant->pivot->is_pinned;

            $chat->participants()->updateExistingPivot($user->id, [
                'is_pinned' => $isPinned
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat ' . ($isPinned ? 'pinned' : 'unpinned') . ' successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pin chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mute/unmute chat
     */
    public function mute(Request $request, $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'duration_hours' => 'sometimes|integer|min:1|max:8760', // Max 1 year
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $chat = Chat::findOrFail($chatId);

            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            $duration = $request->get('duration_hours', 24); // Default 24 hours
            $mutedUntil = now()->addHours($duration);

            $chat->participants()->updateExistingPivot($user->id, [
                'muted_until' => $mutedUntil
            ]);

            return response()->json([
                'success' => true,
                'message' => "Chat muted for {$duration} hours"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mute chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave chat (for group chats)
     */
    public function leave(Request $request, $chatId): JsonResponse
    {
        try {
            $user = $request->user();
            $chat = Chat::findOrFail($chatId);

            if (!$chat->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this chat'
                ], 403);
            }

            if ($chat->isPrivate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot leave private chat'
                ], 400);
            }

            $chat->removeParticipant($user);

            return response()->json([
                'success' => true,
                'message' => 'Left chat successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
