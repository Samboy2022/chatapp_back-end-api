<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Search for users by phone number or email
     */
    public function searchUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:3|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $request->input('q');
            $currentUser = Auth::user();

            // Search users by phone number or email
            $users = User::where(function ($q) use ($query) {
                $q->where('phone_number', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");
            })
            ->where('id', '!=', $currentUser->id) // Exclude current user
            ->limit(20)
            ->get();

            $searchResults = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'is_registered' => true, // All users in database are registered
                    'is_online' => $user->is_online ?? false,
                    'last_seen' => $user->last_seen,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $searchResults
                ],
                'message' => 'Users found successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for users by phone number specifically
     */
    public function searchByPhone(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|min:10|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = $request->input('phone');
            $currentUser = Auth::user();

            $user = User::where('phone_number', $phone)
                       ->where('id', '!=', $currentUser->id)
                       ->first();

            if ($user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'is_registered' => true,
                    'is_online' => $user->is_online ?? false,
                    'last_seen' => $user->last_seen,
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => $userData
                    ],
                    'message' => 'User found successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search by phone',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for users by email specifically
     */
    public function searchByEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');
            $currentUser = Auth::user();

            $user = User::where('email', $email)
                       ->where('id', '!=', $currentUser->id)
                       ->first();

            if ($user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'is_registered' => true,
                    'is_online' => $user->is_online ?? false,
                    'last_seen' => $user->last_seen,
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => $userData
                    ],
                    'message' => 'User found successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search by email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or get existing chat with a user
     */
    public function createOrGetChat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'participant_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $currentUser = Auth::user();
            $participantId = $request->input('participant_id');

            // Check if chat already exists between these two users
            $existingChat = Chat::where('type', 'private')
                ->whereHas('participants', function ($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id);
                })
                ->whereHas('participants', function ($query) use ($participantId) {
                    $query->where('user_id', $participantId);
                })
                ->first();

            if ($existingChat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'chat_id' => $existingChat->id,
                        'is_new' => false
                    ],
                    'message' => 'Existing chat found'
                ]);
            }

            // Create new chat
            $chat = Chat::create([
                'type' => 'private',
                'created_by' => $currentUser->id,
                'is_active' => true,
            ]);

            // Add participants
            $chat->participants()->attach([
                $currentUser->id => ['joined_at' => now()],
                $participantId => ['joined_at' => now()]
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'chat_id' => $chat->id,
                    'is_new' => true
                ],
                'message' => 'New chat created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create or get chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search messages within chats
     */
    public function searchMessages(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:255',
                'chat_id' => 'nullable|exists:chats,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $request->input('q');
            $chatId = $request->input('chat_id');
            $currentUser = Auth::user();

            $messagesQuery = Message::where('content', 'LIKE', "%{$query}%")
                ->whereHas('chat.participants', function ($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id);
                });

            if ($chatId) {
                $messagesQuery->where('chat_id', $chatId);
            }

            $messages = $messagesQuery->with(['chat', 'sender'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $searchResults = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'chat_id' => $message->chat_id,
                    'chat_name' => $message->chat->name ?? $message->sender->name,
                    'sender_name' => $message->sender->name,
                    'created_at' => $message->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $searchResults,
                'message' => 'Messages found successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
