<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\StatusView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusController extends Controller
{
    /**
     * Get all statuses (excluding expired ones)
     * Returns statuses from user's contacts grouped by user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get contact IDs (users who can see this user's status)
            $contactIds = $user->contacts()
                ->where('is_blocked', false)
                ->pluck('contact_user_id')
                ->toArray();
            
            // Include the current user's own statuses
            $contactIds[] = $user->id;
            
            // Get active statuses from contacts
            $statuses = Status::whereIn('user_id', $contactIds)
                ->where('expires_at', '>', Carbon::now())
                ->with(['user:id,name,phone_number,avatar_url', 'views' => function($query) use ($user) {
                    $query->where('viewer_id', $user->id);
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            // Group statuses by user
            $groupedStatuses = $statuses->groupBy('user_id')->map(function ($userStatuses) use ($user) {
                $statusUser = $userStatuses->first()->user;
                $hasUnviewed = $userStatuses->where('user_id', '!=', $user->id)
                    ->filter(function ($status) {
                        return $status->views->isEmpty();
                    })->isNotEmpty();

                return [
                    'user' => [
                        'id' => $statusUser->id,
                        'name' => $statusUser->name,
                        'phone_number' => $statusUser->phone_number,
                        'avatar_url' => $statusUser->avatar_url,
                    ],
                    'statuses' => $userStatuses->map(function ($status) use ($user) {
                        return [
                            'id' => $status->id,
                            'type' => $status->content_type,
                            'content' => $status->content,
                            'media_url' => $status->media_url ? Storage::url($status->media_url) : null,
                            'background_color' => $status->background_color,
                            'text_color' => '#000000',
                            'font_family' => 'Arial',
                            'created_at' => $status->created_at->toISOString(),
                            'expires_at' => $status->expires_at->toISOString(),
                            'is_viewed' => $status->views->isNotEmpty(),
                            'views_count' => $status->views()->count(),
                            'privacy' => 'everyone',
                        ];
                    }),
                    'latest_status_at' => $userStatuses->first()->created_at->toISOString(),
                    'has_unviewed' => $hasUnviewed,
                ];
            })->values();
            
            return response()->json([
                'success' => true,
                'data' => $groupedStatuses,
                'message' => 'Statuses retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new status
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:text,image,video',
                'content' => 'required_if:type,text|string|max:1000',
                'media' => 'required_if:type,image,video|file|max:50000', // 50MB max
                'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'font_style' => 'nullable|string|max:50',
                'text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $statusData = [
                'user_id' => $user->id,
                'content_type' => $request->type,
                'content' => $request->content,
                'background_color' => $request->background_color,
                'font_style' => $request->font_style ?: 'normal',
                'expires_at' => Carbon::now()->addHours(24),
            ];

            // Handle media upload
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('statuses', $filename, 'public');
                $statusData['media_url'] = $path;

                // Generate thumbnail for videos (optional)
                if ($request->type === 'video') {
                    // You can implement video thumbnail generation here
                    // For now, we'll just store the video path
                }
            }

            $status = Status::create($statusData);

            // Load the status with user relationship
            $status->load('user:id,name,avatar_url');

            return response()->json([
                'success' => true,
                'message' => 'Status uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user's statuses
     */
    public function getUserStatuses(Request $request, $userId): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            
            // Check if the user exists
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get user's active statuses
            $statuses = Status::where('user_id', $userId)
                ->where('expires_at', '>', Carbon::now())
                ->with(['views' => function($query) use ($currentUser) {
                    $query->where('viewer_id', $currentUser->id);
                }])
                ->orderBy('created_at', 'asc')
                ->get();

            $statusData = $statuses->map(function ($status) use ($currentUser) {
                return [
                    'id' => $status->id,
                    'type' => $status->content_type,
                    'content' => $status->content,
                    'media_url' => $status->media_url ? Storage::url($status->media_url) : null,
                    'thumbnail_url' => $status->thumbnail_url ? Storage::url($status->thumbnail_url) : null,
                    'background_color' => $status->background_color,
                    'font_style' => $status->font_style,
                    'created_at' => $status->created_at->toISOString(),
                    'expires_at' => $status->expires_at->toISOString(),
                    'time_remaining' => $status->expires_at->diffInSeconds(Carbon::now()),
                    'is_viewed' => $status->views->isNotEmpty(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar_url,
                    ],
                    'statuses' => $statusData,
                ],
                'message' => 'User statuses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a status as viewed
     */
    public function markAsViewed(Request $request, $statusId): JsonResponse
    {
        try {
            $user = Auth::user();
            $status = Status::find($statusId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status not found'
                ], 404);
            }

            // Check if status is expired
            if ($status->expires_at && $status->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status has expired'
                ], 410);
            }

            // Don't track views for own status
            if ($status->user_id === $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Own status view not tracked'
                ]);
            }

            // Create or update view record
            $view = StatusView::firstOrCreate([
                'status_id' => $statusId,
                'viewer_id' => $user->id,
            ], [
                'viewed_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'status_id' => $statusId,
                    'viewed_at' => $view->viewed_at->toISOString(),
                    'is_new_view' => $view->wasRecentlyCreated,
                ],
                'message' => 'Status marked as viewed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark status as viewed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get viewers list for a status (only for status owner)
     */
    public function getViewers(Request $request, $statusId): JsonResponse
    {
        try {
            $user = Auth::user();
            $status = Status::find($statusId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status not found'
                ], 404);
            }

            // Only status owner can see viewers
            if ($status->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view status viewers'
                ], 403);
            }

            $viewers = StatusView::where('status_id', $statusId)
                ->with('viewer:id,name,avatar_url')
                ->orderBy('viewed_at', 'desc')
                ->get()
                ->map(function ($view) {
                    return [
                        'id' => $view->viewer->id,
                        'name' => $view->viewer->name,
                        'avatar' => $view->viewer->avatar_url,
                        'viewed_at' => $view->viewed_at->toISOString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'status_id' => $statusId,
                    'viewers' => $viewers,
                    'total_views' => $viewers->count(),
                ],
                'message' => 'Status viewers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve status viewers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a status (only for status owner)
     */
    public function destroy(Request $request, $statusId): JsonResponse
    {
        try {
            $user = Auth::user();
            $status = Status::find($statusId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status not found'
                ], 404);
            }

            // Only status owner can delete
            if ($status->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this status'
                ], 403);
            }

            // Delete media file if exists
            if ($status->media_url && Storage::disk('public')->exists($status->media_url)) {
                Storage::disk('public')->delete($status->media_url);
            }

            // Delete the status (this will cascade delete views)
            $status->delete();

            return response()->json([
                'success' => true,
                'message' => 'Status deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific status
     */
    public function show($statusId): JsonResponse
    {
        try {
            $user = Auth::user();

            $status = Status::with(['user:id,name,avatar_url'])
                ->findOrFail($statusId);

            // Check if user can view this status
            if ($status->user_id !== $user->id) {
                // Check privacy settings
                if ($status->privacy === 'contacts') {
                    $isContact = $user->contacts()
                        ->where('contact_user_id', $status->user_id)
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

            // Check if status is expired
            if ($status->expires_at <= Carbon::now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status has expired'
                ], 404);
            }

            // Mark as viewed if not own status
            if ($status->user_id !== $user->id) {
                StatusView::firstOrCreate([
                    'status_id' => $status->id,
                    'viewer_id' => $user->id
                ], [
                    'viewed_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $status->id,
                    'content' => $status->content,
                    'type' => $status->content_type,
                    'media_url' => $status->media_url,
                    'background_color' => $status->background_color,
                    'text_color' => '#000000',
                    'privacy' => 'everyone',
                    'created_at' => $status->created_at,
                    'expires_at' => $status->expires_at,
                    'user' => $status->user
                ],
                'message' => 'Status retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
