<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Events\MessageSent;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Requests\Api\StoreMessageRequest;
use App\Http\Requests\Api\UpdateMessageRequest;
use App\Http\Requests\Api\ReactToMessageRequest;
use App\Http\Requests\Api\ForwardMessageRequest;
use App\Http\Requests\Api\SendP2PMessageRequest;

class MessageController extends Controller
{
    /**
     * Get messages for a specific chat
     */
    public function index(Request $request, $chatId): JsonResponse
    {
        $chat = Chat::findOrFail($chatId);

        // Check if user is participant in this chat
        if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat'
            ], 403);
        }

        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);

        $messages = Message::where('chat_id', $chatId)
            ->with([
                'sender:id,name,phone_number,avatar_url',
                'reactions.user:id,name',
                'replyToMessage.sender:id,name',
                'forwardedFrom.sender:id,name'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $messages,
            'message' => 'Messages retrieved successfully'
        ]);
    }

    /**
     * Send a new message
     */
    public function store(StoreMessageRequest $request, $chatId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            
            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            DB::beginTransaction();

            $messageType = $request->type;

            $message = Message::create([
                'chat_id' => $chatId,
                'sender_id' => Auth::id(),
                'message_type' => $messageType,
                'content' => $request->content,
                'media_url' => $request->media_url,
                'media_mime_type' => $request->media_type,
                'media_size' => $request->media_size,
                'media_duration' => $request->media_duration,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_name' => $request->location_name,
                'reply_to_message_id' => $request->reply_to_message_id,
                'status' => 'sent'
            ]);

            // Update chat's last message
            $chat->update([
                'last_message_id' => $message->id,
                'updated_at' => now()
            ]);

            // Mark message as delivered for sender
            $message->update(['status' => 'delivered']);

            DB::commit();

            // Load relationships for response
            $message->load([
                'sender:id,name,phone_number,avatar_url',
                'reactions.user:id,name',
                'replyToMessage.sender:id,name',
                'forwardedFrom.sender:id,name'
            ]);

            // Broadcast the message to all participants
            broadcast(new MessageSent($message, Auth::user()));

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message
                ],
                'message' => 'Message sent successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific message
     */
    public function show($chatId, $messageId): JsonResponse
    {
        $chat = Chat::findOrFail($chatId);

        // Check if user is participant in this chat
        if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this chat'
            ], 403);
        }

        $message = Message::where('chat_id', $chatId)
            ->where('id', $messageId)
            ->with([
                'sender:id,name,phone_number,avatar_url',
                'reactions.user:id,name',
                'replyToMessage.sender:id,name',
                'forwardedFrom.sender:id,name'
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $message,
            'message' => 'Message retrieved successfully'
        ]);
    }

    /**
     * Update a message (edit content)
     */
    public function update(UpdateMessageRequest $request, $chatId, $messageId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            $message = Message::where('chat_id', $chatId)
                ->where('id', $messageId)
                ->firstOrFail();

            // Check if user is the sender
            if ($message->sender_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own messages'
                ], 403);
            }

            // Only allow editing text messages
            if ($message->message_type !== 'text') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only text messages can be edited'
                ], 400);
            }

            $message->update([
                'content' => $request->content,
                'edited_at' => now()
            ]);

            $message->load([
                'sender:id,name,phone_number,avatar_url',
                'reactions.user:id,name',
                'replyToMessage.sender:id,name',
                'forwardedFrom.sender:id,name'
            ]);

            // Broadcast edit event
            try {
                broadcast(new \App\Events\MessageEdited($message))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast MessageEdited: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message
                ],
                'message' => 'Message updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function destroy($chatId, $messageId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            $message = Message::where('chat_id', $chatId)
                ->where('id', $messageId)
                ->firstOrFail();

            // Check if user is the sender or chat admin
            $isAdmin = $chat->participants()
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->exists();

            if ($message->sender_id !== Auth::id() && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own messages or you must be an admin'
                ], 403);
            }

            // Delete associated media file if exists
            if ($message->media_url) {
                $filename = basename(parse_url($message->media_url, PHP_URL_PATH));
                if (Storage::disk('public')->exists('media/' . $filename)) {
                    Storage::disk('public')->delete('media/' . $filename);
                }
            }

            $message->delete();

            // Broadcast delete event
            try {
                broadcast(new \App\Events\MessageDeleted($message->id, $chat->id, true))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast MessageDeleted: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);
            $chat = $message->chat;

            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            // Message is already loaded above

            // Update participant's read status
            $chat->participants()
                ->where('user_id', Auth::id())
                ->update([
                    'last_read_message_id' => $messageId
                ]);

            // Update message status to read if sender is not the current user
            if ($message->sender_id !== Auth::id()) {
                $message->update(['status' => 'read']);

                // Broadcast message read event
                broadcast(new MessageRead($message, Auth::user()));
            }

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking message as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add reaction to message
     */
    public function react(ReactToMessageRequest $request, $chatId, $messageId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            
            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            $message = Message::where('chat_id', $chatId)
                ->where('id', $messageId)
                ->firstOrFail();

            // Check if user already reacted to this message
            $existingReaction = MessageReaction::where('message_id', $messageId)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingReaction) {
                // Update existing reaction
                $existingReaction->update(['emoji' => $request->emoji]);
                $reaction = $existingReaction;
            } else {
                // Create new reaction
                $reaction = MessageReaction::create([
                    'message_id' => $messageId,
                    'user_id' => Auth::id(),
                    'emoji' => $request->emoji
                ]);
            }

            $reaction->load('user:id,name');

            return response()->json([
                'success' => true,
                'data' => $reaction,
                'message' => 'Reaction added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding reaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction($chatId, $messageId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            
            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            $message = Message::where('chat_id', $chatId)
                ->where('id', $messageId)
                ->firstOrFail();

            $deleted = MessageReaction::where('message_id', $messageId)
                ->where('user_id', Auth::id())
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reaction removed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No reaction found to remove'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing reaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all messages for the authenticated user (P2P messaging)
     */
    public function getAllMessages(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);

            // Get all chats where user is a participant
            $chats = Chat::whereHas('participants', function($query) {
                $query->where('user_id', Auth::id());
            })->with(['participants:id,name,phone_number,avatar_url'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $chats,
                'message' => 'Messages retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a P2P message
     */
    public function sendMessage(SendP2PMessageRequest $request): JsonResponse
    {
        try {
            $receiverId = $request->receiver_id;
            $senderId = Auth::id();

            // Don't allow sending message to self
            if ($receiverId == $senderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send message to yourself'
                ], 400);
            }

            // Find or create P2P chat
            $chat = Chat::where('type', 'private')
                ->whereHas('participants', function($query) use ($senderId) {
                    $query->where('user_id', $senderId);
                })
                ->whereHas('participants', function($query) use ($receiverId) {
                    $query->where('user_id', $receiverId);
                })
                ->first();

            if (!$chat) {
                // Create new P2P chat
                $chat = Chat::create([
                    'type' => 'private',
                    'created_by' => $senderId
                ]);

                // Add participants
                $chat->participants()->attach($senderId);
                $chat->participants()->attach($receiverId);
            }

            // Create message
            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $senderId,
                'content' => $request->message,
                'message_type' => $request->type,
                'reply_to_message_id' => $request->reply_to_id,
                'media_url' => $request->media_url,
                'media_mime_type' => $request->media_type,
                'media_size' => $request->media_size,
                'media_duration' => $request->duration
            ]);

            // Update chat's last message
            $chat->update([
                'last_message_id' => $message->id,
                'updated_at' => now()
            ]);

            // Load relationships
            $message->load(['sender:id,name,phone_number,avatar_url', 'replyToMessage.sender:id,name']);

            // Broadcast message
            broadcast(new MessageSent($message, $message->sender))->toOthers();

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message
                ],
                'message' => 'Message sent successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation with a specific user
     */
    public function getConversation(Request $request, $userId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            // Validate that the user exists
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Find P2P chat between current user and specified user
            $chat = Chat::where('type', 'private')
                ->whereHas('participants', function($query) use ($currentUserId) {
                    $query->where('user_id', $currentUserId);
                })
                ->whereHas('participants', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [],
                        'current_page' => 1,
                        'per_page' => 50,
                        'total' => 0
                    ],
                    'message' => 'No conversation found'
                ]);
            }

            $perPage = $request->get('per_page', 50);

            $messages = Message::where('chat_id', $chat->id)
                ->with([
                    'sender:id,name,phone_number,avatar_url',
                    'reactions.user:id,name',
                    'replyToMessage.sender:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $messages,
                'message' => 'Conversation retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add reaction to message
     */
    public function addReaction(Request $request, $messageId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reaction' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = Message::findOrFail($messageId);
            $chat = $message->chat;

            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            // Check if user already reacted to this message
            $existingReaction = \App\Models\MessageReaction::where('message_id', $messageId)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingReaction) {
                // Update existing reaction
                $existingReaction->update(['emoji' => $request->reaction]);
                $reaction = $existingReaction;
            } else {
                // Create new reaction
                $reaction = \App\Models\MessageReaction::create([
                    'message_id' => $messageId,
                    'user_id' => Auth::id(),
                    'emoji' => $request->reaction
                ]);
            }

            $reaction->load('user:id,name');

            return response()->json([
                'success' => true,
                'data' => [
                    'reaction' => $reaction
                ],
                'message' => 'Reaction added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding reaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forward a message to another chat
     */
    public function forwardMessage(ForwardMessageRequest $request): JsonResponse
    {
        try {
            $originalMessage = Message::findOrFail($request->message_id);
            $targetChat = Chat::findOrFail($request->target_chat_id);
            $sourceChat = $originalMessage->chat;

            // Check if user is participant in both chats
            if (!$sourceChat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in the source chat'
                ], 403);
            }

            if (!$targetChat->participants()->where('user_id', Auth::id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in the target chat'
                ], 403);
            }

            DB::beginTransaction();

            // Create the forwarded message
            $forwardedMessage = Message::create([
                'chat_id' => $request->target_chat_id,
                'sender_id' => Auth::id(),
                'message_type' => $originalMessage->message_type,
                'content' => $request->additional_text ?? $originalMessage->content,
                'media_url' => $originalMessage->media_url,
                'media_size' => $originalMessage->media_size,
                'media_duration' => $originalMessage->media_duration,
                'media_mime_type' => $originalMessage->media_mime_type,
                'file_name' => $originalMessage->file_name,
                'thumbnail_url' => $originalMessage->thumbnail_url,
                'latitude' => $originalMessage->latitude,
                'longitude' => $originalMessage->longitude,
                'location_name' => $originalMessage->location_name,
                'contact_data' => $originalMessage->contact_data,
                'forwarded_from_id' => $originalMessage->id,
                'is_forwarded' => true,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update target chat's last message and timestamp
            $targetChat->update([
                'last_message_id' => $forwardedMessage->id,
                'updated_at' => now()
            ]);

            DB::commit();

            // Load relationships for response
            $forwardedMessage->load([
                'sender:id,name,phone_number,avatar_url',
                'chat:id,name,type',
                'forwardedFrom.sender:id,name'
            ]);

            // Broadcast the message to chat participants
            broadcast(new MessageSent($forwardedMessage, Auth::user()))->toOthers();

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $forwardedMessage,
                    'original_message_id' => $originalMessage->id
                ],
                'message' => 'Message forwarded successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error forwarding message: ' . $e->getMessage()
            ], 500);
        }
    }
}
