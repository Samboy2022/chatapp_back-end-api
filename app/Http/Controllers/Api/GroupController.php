<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\StoreGroupRequest;
use App\Http\Requests\Api\SendGroupMessageRequest;

class GroupController extends Controller
{
    /**
     * Get all groups for the authenticated user
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            $groups = Chat::where('type', 'group')
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['participants', 'creator'])
                ->orderBy('updated_at', 'desc')
                ->get();

            $groupsData = $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'avatar' => $group->avatar_url,
                    'created_by' => $group->created_by,
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                    'members' => $group->participants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'avatar' => $participant->avatar_url,
                            'phone_number' => $participant->phone_number,
                            'is_admin' => $participant->pivot->role === 'admin',
                            'role' => $participant->pivot->role,
                            'joined_at' => $participant->pivot->joined_at,
                        ];
                    }),
                    'messages' => [], // Messages will be loaded separately
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $groupsData,
                'message' => 'Groups retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new group
     */
    public function store(StoreGroupRequest $request)
    {
        try {
            $user = Auth::user();
            
            // Create the group chat
            $group = Chat::create([
                'type' => 'group',
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => $user->id,
                'is_active' => true,
            ]);

            // Handle avatar upload - store path for full URL generation on deployment
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('group_avatars', 'public');
                $group->avatar_url = $avatarPath; // Stored path, Chat model returns full URL
                $group->save();
            }

            // Add creator as admin
            $group->participants()->attach($user->id, [
                'role' => 'admin',
                'joined_at' => now(),
            ]);

            // Add other participants
            if ($request->has('participants') && is_array($request->participants)) {
                foreach ($request->participants as $participantId) {
                    if ($participantId != $user->id) {
                        $group->participants()->attach($participantId, [
                            'role' => 'member',
                            'joined_at' => now(),
                        ]);
                    }
                }
            }

            // Load the group with relationships
            $group->load(['participants', 'creator']);

            $groupData = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'avatar' => $group->avatar_url,
                'created_by' => $group->created_by,
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
                'members' => $group->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'avatar' => $participant->avatar_url,
                        'phone_number' => $participant->phone_number,
                        'is_admin' => $participant->pivot->role === 'admin',
                        'role' => $participant->pivot->role,
                        'joined_at' => $participant->pivot->joined_at,
                    ];
                }),
                'messages' => [],
            ];

            return response()->json([
                'success' => true,
                'data' => $groupData,
                'message' => 'Group created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific group
     */
    public function show($groupId)
    {
        try {
            $user = Auth::user();
            
            $group = Chat::where('type', 'group')
                ->where('id', $groupId)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['participants', 'creator'])
                ->first();

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found or access denied'
                ], 404);
            }

            $groupData = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'avatar' => $group->avatar_url,
                'created_by' => $group->created_by,
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
                'members' => $group->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'avatar' => $participant->avatar_url,
                        'phone_number' => $participant->phone_number,
                        'is_admin' => $participant->pivot->role === 'admin',
                        'role' => $participant->pivot->role,
                        'joined_at' => $participant->pivot->joined_at,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $groupData,
                'message' => 'Group retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add members to a group
     */
    public function addMembers(\Illuminate\Http\Request $request, $groupId)
    {
        try {
            $user = Auth::user();
            
            $group = Chat::where('type', 'group')
                ->where('id', $groupId)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('role', 'admin');
                })
                ->first();

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found or insufficient permissions'
                ], 404);
            }

            $requested = collect($request->participants ?? [])->unique();

            // Only count people who aren't already in — re-adding an existing
            // member shouldn't count against the cap.
            $existingIds = $group->participants()->pluck('users.id');
            $newIds = $requested->reject(fn ($id) => $existingIds->contains($id));

            $maxGroupSize = (int) (\App\Models\Setting::get('max_group_size', 256) ?: 256);
            $resultingSize = $existingIds->count() + $newIds->count();

            if ($resultingSize > $maxGroupSize) {
                $room = max(0, $maxGroupSize - $existingIds->count());

                return response()->json([
                    'success' => false,
                    'message' => $room === 0
                        ? "This group is full ({$maxGroupSize} members)."
                        : "You can only add {$room} more member(s) — the limit is {$maxGroupSize}.",
                ], 422);
            }

            foreach ($newIds as $participantId) {
                $group->participants()->attach($participantId, [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Members added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a member from a group
     */
    public function removeMember($groupId, $memberId)
    {
        try {
            $user = Auth::user();
            
            $group = Chat::where('type', 'group')
                ->where('id', $groupId)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('role', 'admin');
                })
                ->first();

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found or insufficient permissions'
                ], 404);
            }

            // Remove the member
            $group->participants()->detach($memberId);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave a group
     */
    public function leave($groupId)
    {
        try {
            $user = Auth::user();
            
            $group = Chat::where('type', 'group')
                ->where('id', $groupId)
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ], 404);
            }

            // Remove user from group
            $group->participants()->detach($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Left group successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a single user to group
     */
    public function addUser(\Illuminate\Http\Request $request, $groupId)
    {
        try {
            $group = Chat::where('type', 'group')->findOrFail($groupId);
            $currentUser = Auth::user();

            // Check if current user is admin or creator
            $currentParticipant = $group->participants()->where('user_id', $currentUser->id)->first();
            if (!$currentParticipant || ($currentParticipant->pivot->role !== 'admin' && $group->created_by !== $currentUser->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add users to this group. Only admins can add new members.',
                    'debug_info' => [
                        'current_user_role' => $currentParticipant ? $currentParticipant->pivot->role : 'not_participant',
                        'is_creator' => $group->created_by === $currentUser->id,
                        'group_creator_id' => $group->created_by
                    ]
                ], 403);
            }

            $userId = $request->user_id;

            // Check if user is already in group
            if ($group->participants()->where('user_id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this group',
                    'debug_info' => [
                        'user_id' => $userId,
                        'group_id' => $groupId
                    ]
                ], 400);
            }

            // Add user to group
            $group->participants()->attach($userId, [
                'role' => 'member',
                'joined_at' => now()
            ]);

            $addedUser = User::find($userId);

            return response()->json([
                'success' => true,
                'message' => 'User added to group successfully',
                'data' => [
                    'user' => [
                        'id' => $addedUser->id,
                        'name' => $addedUser->name,
                        'phone_number' => $addedUser->phone_number,
                        'avatar_url' => $addedUser->avatar_url
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add user to group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a single user from group
     */
    public function removeUser(Request $request, $groupId, $userId)
    {
        try {
            $group = Chat::where('type', 'group')->findOrFail($groupId);
            $currentUser = Auth::user();

            // Check if current user is admin or creator
            $currentParticipant = $group->participants()->where('user_id', $currentUser->id)->first();
            if (!$currentParticipant || ($currentParticipant->pivot->role !== 'admin' && $group->created_by !== $currentUser->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to remove users from this group'
                ], 403);
            }

            // Check if user is in group
            if (!$group->participants()->where('user_id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not in the group'
                ], 400);
            }

            // Cannot remove group creator
            if ($group->created_by == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove group creator'
                ], 400);
            }

            // Remove user from group
            $group->participants()->detach($userId);

            return response()->json([
                'success' => true,
                'message' => 'User removed from group successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove user from group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message to group
     */
    public function sendMessage(SendGroupMessageRequest $request, $groupId)
    {
        try {
            $group = Chat::where('type', 'group')->findOrFail($groupId);
            $currentUser = Auth::user();

            // Check if user is member of group
            if (!$group->participants()->where('user_id', $currentUser->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this group'
                ], 403);
            }

            // Create message using correct column names
            $message = \App\Models\Message::create([
                'chat_id' => $group->id,
                'sender_id' => $currentUser->id,
                'content' => $request->message,
                'message_type' => $request->type,
                'reply_to_message_id' => $request->reply_to_id,
                'media_url' => $request->media_url,
                'media_mime_type' => $request->media_type,
                'media_size' => $request->media_size,
                'media_duration' => $request->duration
            ]);

            // Update group's last message
            $group->update([
                'last_message_id' => $message->id,
                'updated_at' => now()
            ]);

            // Load relationships
            $message->load(['sender:id,name,phone_number,avatar_url', 'replyToMessage.sender:id,name']);

            // Broadcast message to group members
            broadcast(new \App\Events\GroupMessageSent($message, $group))->toOthers();

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Group message sent successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send group message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
