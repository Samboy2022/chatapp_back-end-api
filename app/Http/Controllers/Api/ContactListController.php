<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactListController extends Controller
{
    /**
     * Get all users for contact discovery
     * Any authenticated user can discover and chat with any other user
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $currentUser = auth()->user();
            $perPage = min($request->input('per_page', 20), 100); // Max 100 per page
            $page = $request->input('page', 1);

            // Get all users except current user, ordered by online status and last seen
            $users = User::where('id', '!=', $currentUser->id)
                ->where('is_active', true)
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'avatar_url',
                    'is_online',
                    'last_seen_at',
                    'created_at'
                ])
                ->orderBy('is_online', 'desc') // Online users first
                ->orderBy('last_seen_at', 'desc')
                ->orderBy('name')
                ->paginate($perPage);

            // Format users for response
            $formattedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar_url,
                    'is_online' => $user->is_online ?? false,
                    'last_seen' => $user->last_seen_at?->diffForHumans(),
                    'can_chat' => true, // Any user can chat with any other user
                    'member_since' => $user->created_at?->format('M Y'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $formattedUsers,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'last_page' => $users->lastPage(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                    ]
                ],
                'message' => 'Users retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get online users only
     */
    public function getOnlineUsers(Request $request): JsonResponse
    {
        try {
            $currentUser = auth()->user();
            $perPage = min($request->input('per_page', 20), 100);

            $users = User::where('id', '!=', $currentUser->id)
                ->where('is_active', true)
                ->where('is_online', true)
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'avatar_url',
                    'is_online',
                    'last_seen_at'
                ])
                ->orderBy('last_seen_at', 'desc')
                ->paginate($perPage);

            $formattedUsers = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar_url,
                    'is_online' => true,
                    'last_seen' => 'Online now',
                    'can_chat' => true,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $formattedUsers,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'last_page' => $users->lastPage(),
                    ]
                ],
                'message' => 'Online users retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve online users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users by name, phone, or email
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q');
            $currentUser = auth()->user();

            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 422);
            }

            // Search users by phone number, email, or name
            $users = User::where(function ($q) use ($query) {
                $q->where('phone_number', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");
            })
            ->where('id', '!=', $currentUser->id) // Exclude current user
            ->where('is_active', true)
            ->limit(50)
            ->get();

            $searchResults = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar_url,
                    'is_online' => $user->is_online ?? false,
                    'last_seen' => $user->last_seen_at?->diffForHumans(),
                    'can_chat' => true,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $searchResults,
                    'total' => $searchResults->count()
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
}