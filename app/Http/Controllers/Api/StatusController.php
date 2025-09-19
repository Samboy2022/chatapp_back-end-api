<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\StatusView;
use App\Models\Contact;
use App\Events\StatusUploaded;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StatusController extends Controller
{
    /**
     * Get status updates feed for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        Log::info('StatusController: index method called');
        try {
            $user = Auth::user();
            
            // Get contacts who are not blocked
            $allowedContactIds = Contact::where('user_id', $user->id)
                ->where('is_blocked', false)
                ->pluck('contact_user_id')
                ->toArray();
            
            // Add self to the list
            $allowedContactIds[] = $user->id;
            
            // Get recent statuses (within 24 hours) from allowed contacts
            $statuses = Status::whereIn('user_id', $allowedContactIds)
                ->where('expires_at', '>', now())
                ->with([
                    'user:id,name,phone_number,avatar_url',
                    'views' => function($query) use ($user) {
                        $query->where('viewer_id', $user->id);
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            // Format the response
            $statusesData = [];
            foreach ($statuses as $userId => $userStatuses) {
                $firstStatus = $userStatuses->first();
                $statusesData[] = [
                    'user' => $firstStatus->user,
                    'statuses' => $userStatuses->map(function($status) {
                        return [
                            'id' => $status->id,
                            'type' => $status->type,
                            'content' => $status->content,
                            'media_url' => $status->media_url,
                            'background_color' => $status->background_color,
                            'text_color' => $status->text_color,
                            'font_family' => $status->font_family,
                            'created_at' => $status->created_at,
                            'expires_at' => $status->expires_at,
                            'is_viewed' => $status->views->count() > 0,
                            'views_count' => $status->views_count,
                            'privacy' => $status->privacy,
                        ];
                    }),
                    'latest_status_at' => $userStatuses->max('created_at'),
                    'has_unviewed' => $userStatuses->where('views', '[]')->count() > 0
                ];
            }

            // Sort by latest status
            usort($statusesData, function($a, $b) {
                return $b['latest_status_at'] <=> $a['latest_status_at'];
            });

            return response()->json([
                'success' => true,
                'data' => $statusesData,
                'message' => 'Status feed retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving status feed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving status feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new status update
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('StatusController: store method called');
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:text,image,video',
                'content' => 'required_if:type,text|nullable|string|max:1000',
                'media_url' => 'required_if:type,image,video|nullable|string',
                'background_color' => 'nullable|string',
                'text_color' => 'nullable|string',
                'font_family' => 'nullable|string',
                'privacy' => 'required|string|in:everyone,contacts,close_friends',
                'duration' => 'nullable|integer|min:1|max:30' // seconds for video
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Calculate expiry time (24 hours from now)
            $expiresAt = now()->addHours(24);

            $status = Status::create([
                'user_id' => Auth::id(),
                'content_type' => $request->type,
                'content' => $request->content,
                'media_url' => $request->media_url,
                'background_color' => $request->background_color,
                'font_style' => $request->font_family,
                'expires_at' => $expiresAt
            ]);

            $status->load('user:id,name,phone_number,avatar_url');

            // Broadcast status to relevant users
            broadcast(new StatusUploaded($status, Auth::user()))->toOthers();

            return response()->json([
                'success' => true,
                'data' => $status,
                'message' => 'Status uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error uploading status: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific status
     */
    public function show($statusId): JsonResponse
    {
        try {
            $status = Status::with([
                'user:id,name,phone_number,avatar_url',
                'views.viewer:id,name,avatar_url'
            ])->findOrFail($statusId);

            // Check if status has expired
            if ($status->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status has expired'
                ], 410);
            }

            // Check privacy permissions
            $user = Auth::user();
            if ($status->user_id !== $user->id) {
                if ($status->privacy === 'contacts') {
                    $isContact = Contact::where('user_id', $status->user_id)
                        ->where('contact_id', $user->id)
                        ->where('is_blocked', false)
                        ->exists();
                    
                    if (!$isContact) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to view this status'
                        ], 403);
                    }
                }
            }

            // Mark as viewed if not already viewed by current user
            if ($status->user_id !== $user->id) {
                StatusView::firstOrCreate([
                    'status_id' => $statusId,
                    'viewer_id' => $user->id
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $status,
                'message' => 'Status retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a status (only owner can delete)
     */
    public function destroy($statusId): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);

            // Check if user is the owner
            if ($status->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own status'
                ], 403);
            }

            // Delete associated media file if exists
            if ($status->media_url) {
                $filename = basename(parse_url($status->media_url, PHP_URL_PATH));
                if (Storage::disk('public')->exists('status/' . $filename)) {
                    Storage::disk('public')->delete('status/' . $filename);
                }
            }

            $status->delete();

            return response()->json([
                'success' => true,
                'message' => 'Status deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a status as viewed
     */
    public function markAsViewed($statusId): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);

            // Check if status has expired
            if ($status->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status has expired'
                ], 410);
            }

            // Don't allow users to view their own status
            if ($status->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot view your own status'
                ], 400);
            }

            // Check privacy permissions
            $user = Auth::user();
            if ($status->privacy === 'contacts') {
                $isContact = Contact::where('user_id', $status->user_id)
                    ->where('contact_id', $user->id)
                    ->where('is_blocked', false)
                    ->exists();
                
                if (!$isContact) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view this status'
                    ], 403);
                }
            }

            // Create view record
            $view = StatusView::firstOrCreate([
                'status_id' => $statusId,
                'viewer_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $view,
                'message' => 'Status marked as viewed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking status as viewed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statuses for a specific user
     */
    public function getUserStatuses($userId): JsonResponse
    {
        try {
            $targetUser = \App\Models\User::findOrFail($userId);
            $currentUser = Auth::user();

            // Check if current user can view target user's statuses
            if ($userId != $currentUser->id) {
                // Check if they are contacts
                $isContact = Contact::where('user_id', $userId)
                    ->where('contact_id', $currentUser->id)
                    ->where('is_blocked', false)
                    ->exists();

                if (!$isContact) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view this user\'s statuses'
                    ], 403);
                }
            }

            // Get non-expired statuses
            $statuses = Status::where('user_id', $userId)
                ->where('expires_at', '>', now())
                ->where(function($query) use ($currentUser, $userId) {
                    if ($userId === $currentUser->id) {
                        // Own statuses - show all
                        return;
                    } else {
                        // Other user's statuses - respect privacy
                        $query->where('privacy', 'everyone')
                              ->orWhere('privacy', 'contacts');
                    }
                })
                ->with([
                    'views' => function($query) use ($currentUser) {
                        $query->where('viewer_id', $currentUser->id);
                    },
                    'views.viewer:id,name,avatar_url'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // If viewing own statuses, include view details
            if ($userId == $currentUser->id) {
                $statuses->load('views.viewer:id,name,avatar_url');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $targetUser->only(['id', 'name', 'phone_number', 'avatar_url']),
                    'statuses' => $statuses
                ],
                'message' => 'User statuses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user statuses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status viewers (only for own statuses)
     */
    public function getViewers($statusId): JsonResponse
    {
        try {
            $status = Status::findOrFail($statusId);

            // Check if user is the owner
            if ($status->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view viewers of your own status'
                ], 403);
            }

            $viewers = StatusView::where('status_id', $statusId)
                ->with('viewer:id,name,phone_number,avatar_url')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($view) {
                    return [
                        'id' => $view->viewer->id,
                        'name' => $view->viewer->name,
                        'phone_number' => $view->viewer->phone_number,
                        'avatar_url' => $view->viewer->avatar_url,
                        'viewed_at' => $view->viewed_at ? $view->viewed_at->toISOString() : $view->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $viewers,
                'message' => 'Status viewers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving status viewers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up expired statuses (can be called via cron job)
     */
    public function cleanupExpired(): JsonResponse
    {
        try {
            $expiredStatuses = Status::where('expires_at', '<', now())->get();
            
            foreach ($expiredStatuses as $status) {
                // Delete associated media file if exists
                if ($status->media_url) {
                    $filename = basename(parse_url($status->media_url, PHP_URL_PATH));
                    if (Storage::disk('public')->exists('status/' . $filename)) {
                        Storage::disk('public')->delete('status/' . $filename);
                    }
                }
            }

            $deletedCount = Status::where('expires_at', '<', now())->delete();

            return response()->json([
                'success' => true,
                'data' => ['deleted_count' => $deletedCount],
                'message' => 'Expired statuses cleaned up successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning up expired statuses: ' . $e->getMessage()
            ], 500);
        }
    }
}
