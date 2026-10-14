<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\StoreChatRequest;
use App\Http\Requests\Api\UpdateChatRequest;
use App\Http\Requests\Api\MuteChatRequest;
use App\Http\Requests\Api\UpdateChatPictureRequest;

class ChatController extends Controller
{
    /**
     * Get user's chats
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // The chat list poll is how new messages reach the device, so
            // this is where they become "delivered" (two grey ticks).
            app(\App\Services\MessageReceiptService::class)->markDelivered($user);

            $chats = $user->chats()
                ->with([
                    'latestMessage.sender:id,name,avatar_url',
                    'participants:id,name,avatar_url,is_online,last_seen_at',
                    'creator:id,name,avatar_url'
                ])
                ->withPivot(['is_archived', 'is_pinned', 'muted_until', 'last_read_message_id', 'cleared_at', 'deleted_at'])
                ->orderBy('updated_at', 'desc')
                ->get()
                // A deleted chat stays gone until someone writes in it again
                // (as in WhatsApp). deleted_at was stored and never checked, so
                // deleted chats reappeared on the next refresh.
                ->filter(function ($chat) {
                    $deletedAt = $chat->pivot->deleted_at;
                    $latest = $chat->latestMessage?->created_at;
                    return !$deletedAt || ($latest && $latest->gt(\Carbon\Carbon::parse($deletedAt)));
                })
                ->values()
                ->map(function ($chat) use ($user) {
                    // Don't preview a message the user cleared or deleted.
                    $hiddenBefore = collect([$chat->pivot->cleared_at, $chat->pivot->deleted_at])->filter()->max();
                    if ($hiddenBefore && $chat->latestMessage
                        && !$chat->latestMessage->created_at->gt(\Carbon\Carbon::parse($hiddenBefore))) {
                        $chat->setRelation('latestMessage', null);
                    }

                    $chat->unread_count = $chat->getUnreadCount($user);
                    
                    // Fix: Properly handle muted_until from pivot data
                    $chat->is_muted = false;
                    $chat->muted_until = null;
                    if ($chat->pivot->muted_until) {
                        try {
                            $mutedUntil = \Carbon\Carbon::parse($chat->pivot->muted_until);
                            $chat->is_muted = $mutedUntil->isFuture();
                            // So the app can say "Muted until …".
                            $chat->muted_until = $chat->is_muted ? $mutedUntil->toISOString() : null;
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
    public function store(StoreChatRequest $request): JsonResponse
    {
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
                // Respect the admin-configured cap before creating anything.
                $maxGroupSize = (int) (\App\Models\Setting::get('max_group_size', 256) ?: 256);

                if (count($participantIds) > $maxGroupSize) {
                    return response()->json([
                        'success' => false,
                        'message' => "A group can have at most {$maxGroupSize} members.",
                        'errors' => ['participants' => ["Maximum group size is {$maxGroupSize}."]],
                    ], 422);
                }

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
    public function update(UpdateChatRequest $request, $chatId): JsonResponse
    {
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

            $updateData = $request->only(['name', 'description']);
            
            // Handle both avatar_url and group_picture field names
            if ($request->has('avatar_url')) {
                $updateData['avatar_url'] = $request->avatar_url;
            }
            if ($request->has('group_picture')) {
                $updateData['avatar_url'] = $request->group_picture;
            }

            $chat->update($updateData);

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
    public function mute(MuteChatRequest $request, $chatId): JsonResponse
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

            if (!$request->boolean('mute', true)) {
                $chat->participants()->updateExistingPivot($user->id, ['muted_until' => null]);

                return response()->json([
                    'success' => true,
                    'data' => ['is_muted' => false, 'muted_until' => null, 'always' => false],
                    'message' => 'Chat unmuted'
                ], 200);
            }

            // "Always" is stored as a far-future date: muted_until is the only
            // column, and everything that checks it compares against now. It
            // is a TIMESTAMP, which ends in January 2038 — later dates fail.
            $always = $request->boolean('always');
            $mutedUntil = match (true) {
                $always => \Carbon\Carbon::create(2037, 12, 31, 23, 59, 59),
                $request->filled('duration_minutes') => now()->addMinutes((int) $request->duration_minutes),
                default => now()->addHours((int) $request->get('duration_hours', 24)),
            };

            $chat->participants()->updateExistingPivot($user->id, [
                'muted_until' => $mutedUntil
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_muted' => true,
                    'muted_until' => $mutedUntil->toISOString(),
                    'always' => $always,
                ],
                'message' => $always ? 'Chat muted' : 'Chat muted until ' . $mutedUntil->toDayDateTimeString()
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
     * Clear chat messages for current user
     */
    public function clearChat(Request $request, $chatId): JsonResponse
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

            $chat->participants()->updateExistingPivot($user->id, [
                'cleared_at' => now(),
                // Nothing hidden should still count as unread.
                'last_read_message_id' => $chat->messages()->max('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat cleared successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update chat picture/avatar
     */
    public function updatePicture(UpdateChatPictureRequest $request, $chatId): JsonResponse
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

            // Only admins can update group picture
            if ($chat->isGroup()) {
                $participant = $chat->participants()->where('users.id', $user->id)->first();
                if ($participant->pivot->role !== 'admin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only admins can update group picture'
                    ], 403);
                }
            }

            $avatarUrl = $request->avatar_url ?? $request->group_picture;
            if ($avatarUrl) {
                $chat->update(['avatar_url' => $avatarUrl]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat picture updated successfully',
                'data' => [
                    'chat' => $chat,
                    'avatar_url' => $chat->avatar_url,
                    'group_picture' => $chat->avatar_url,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update chat picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove chat picture/avatar
     */
    public function removePicture(Request $request, $chatId): JsonResponse
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

            // Only admins can remove group picture
            if ($chat->isGroup()) {
                $participant = $chat->participants()->where('users.id', $user->id)->first();
                if ($participant->pivot->role !== 'admin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only admins can remove group picture'
                    ], 403);
                }
            }

            $chat->update(['avatar_url' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Chat picture removed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove chat picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete chat for current user (soft delete)
     */
    public function deleteChat(Request $request, $chatId): JsonResponse
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

            $chat->participants()->updateExistingPivot($user->id, [
                'deleted_at' => now(),
                'last_read_message_id' => $chat->messages()->max('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete chat',
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
