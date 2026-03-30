<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Events\UserTyping;
use App\Events\UserOnlineStatusChanged;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebSocketController extends Controller
{
    /**
     * Handle typing indicator
     */
    public function typing(Request $request, $chatId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_typing' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $chat = Chat::findOrFail($chatId);
            $user = Auth::user();

            // Check if user is participant in this chat
            if (!$chat->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            // Broadcast typing event
            broadcast(new UserTyping($user, $chat, $request->is_typing))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Typing status updated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating typing status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user online status
     */
    public function updateOnlineStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_online' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $isOnline = $request->is_online;

            // Update user status
            $user->update([
                'is_online' => $isOnline,
                'last_seen_at' => now(),
            ]);

            // Broadcast status change
            broadcast(new UserOnlineStatusChanged($user, $isOnline));

            return response()->json([
                'success' => true,
                'message' => 'Online status updated',
                'data' => [
                    'is_online' => $user->is_online,
                    'last_seen_at' => $user->last_seen_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating online status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markMessageAsRead(Request $request, $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);
            $user = Auth::user();

            // Check if user is participant in the chat
            if (!$message->chat->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this chat'
                ], 403);
            }

            // Don't mark own messages as read
            if ($message->sender_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark your own message as read'
                ], 400);
            }

            // Update message read status
            $message->update(['read_at' => now()]);

            // Update participant's last read message
            $message->chat->participants()
                ->where('user_id', $user->id)
                ->update(['last_read_message_id' => $message->id]);

            // Broadcast message read event
            broadcast(new MessageRead($message, $user));

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read',
                'data' => [
                    'message_id' => $message->id,
                    'read_at' => $message->read_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking message as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get WebSocket connection info
     */
    public function getConnectionInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'host' => config('reverb.servers.reverb.hostname') ?: config('reverb.servers.reverb.host'),
                'port' => config('reverb.apps.apps.0.options.port'),
                'scheme' => config('reverb.apps.apps.0.options.scheme'),
                'app_key' => config('reverb.apps.apps.0.key'),
                'auth_endpoint' => url('/broadcasting/auth'),
            ]
        ]);
    }

    /**
     * Get user's active chats for WebSocket subscription
     */
    public function getActiveChats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $chats = $user->chats()
                ->with(['participants:id,name,avatar_url,is_online,last_seen_at'])
                ->select('chats.id', 'chats.type', 'chats.name', 'chats.avatar_url', 'chats.updated_at')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $chats,
                'message' => 'Active chats retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving active chats: ' . $e->getMessage()
            ], 500);
        }
    }
}
