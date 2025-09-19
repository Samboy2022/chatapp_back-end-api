<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    /**
     * Display a listing of status updates
     */
    public function index(Request $request)
    {
        $query = Status::with(['user', 'views.viewer'])
                      ->withCount('views');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by content type
        if ($request->filled('content_type')) {
            $query->where('content_type', $request->content_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status (active/expired)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('expires_at', '>', now());
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $statuses = $query->paginate(30)->withQueryString();

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.statuses.index', compact('statuses', 'users'));
    }

    /**
     * Show the form for creating a new status
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.statuses.create', compact('users'));
    }

    /**
     * Store a newly created status
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content_type' => 'required|in:text,image,video',
            'content' => 'required_if:content_type,text|nullable|string|max:500',
            'media' => 'required_unless:content_type,text|nullable|file|max:100000', // 100MB max
            'background_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_style' => 'nullable|string|max:50',
            'expires_in_hours' => 'required|integer|min:1|max:168', // Max 7 days
        ]);

        $data = [
            'user_id' => $request->user_id,
            'content_type' => $request->content_type,
            'content' => $request->content,
            'background_color' => $request->background_color,
            'font_style' => $request->font_style,
            'expires_at' => now()->addHours($request->expires_in_hours),
        ];

        // Handle media upload
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = 'status_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_media', $filename, 'public');
            
            $data['media_url'] = Storage::disk('public')->url($path);
            
            // Generate thumbnail for videos and images
            if (in_array($request->content_type, ['image', 'video'])) {
                // In a real app, you'd generate thumbnails here
                $data['thumbnail_url'] = $data['media_url'];
            }
        }

        Status::create($data);

        return redirect()->route('admin.statuses.index')
                        ->with('success', 'Status created successfully!');
    }

    /**
     * Display the specified status
     */
    public function show(Status $status)
    {
        $status->load(['user', 'views.viewer']);
        
        // Status statistics
        $stats = [
            'total_views' => $status->views->count(),
            'unique_viewers' => $status->views->unique('viewer_id')->count(),
            'views_today' => $status->views()->whereDate('created_at', today())->count(),
            'time_remaining' => $status->expires_at > now() ? $status->expires_at->diffForHumans() : 'Expired',
            'is_active' => $status->expires_at > now(),
        ];

        return view('admin.statuses.show', compact('status', 'stats'));
    }

    /**
     * Show the form for editing the specified status
     */
    public function edit(Status $status)
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.statuses.edit', compact('status', 'users'));
    }

    /**
     * Update the specified status
     */
    public function update(Request $request, Status $status)
    {
        $request->validate([
            'content' => 'required_if:content_type,text|nullable|string|max:500',
            'background_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_style' => 'nullable|string|max:50',
            'expires_in_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $data = [
            'content' => $request->content,
            'background_color' => $request->background_color,
            'font_style' => $request->font_style,
        ];

        // Update expiration if provided
        if ($request->filled('expires_in_hours')) {
            $data['expires_at'] = now()->addHours($request->expires_in_hours);
        }

        $status->update($data);

        return redirect()->route('admin.statuses.show', $status)
                        ->with('success', 'Status updated successfully!');
    }

    /**
     * Remove the specified status
     */
    public function destroy(Status $status)
    {
        // Delete associated media
        if ($status->media_url) {
            $path = str_replace('/storage/', '', parse_url($status->media_url, PHP_URL_PATH));
            Storage::disk('public')->delete($path);
        }
        if ($status->thumbnail_url && $status->thumbnail_url !== $status->media_url) {
            $path = str_replace('/storage/', '', parse_url($status->thumbnail_url, PHP_URL_PATH));
            Storage::disk('public')->delete($path);
        }

        $status->delete();

        return redirect()->route('admin.statuses.index')
                        ->with('success', 'Status deleted successfully!');
    }

    /**
     * Cleanup expired statuses
     */
    public function cleanupExpired()
    {
        $expiredStatuses = Status::where('expires_at', '<=', now())->get();
        
        foreach ($expiredStatuses as $status) {
            // Delete associated media
            if ($status->media_url) {
                $path = str_replace('/storage/', '', parse_url($status->media_url, PHP_URL_PATH));
                Storage::disk('public')->delete($path);
            }
            if ($status->thumbnail_url && $status->thumbnail_url !== $status->media_url) {
                $path = str_replace('/storage/', '', parse_url($status->thumbnail_url, PHP_URL_PATH));
                Storage::disk('public')->delete($path);
            }
        }

        $count = $expiredStatuses->count();
        Status::where('expires_at', '<=', now())->delete();

        return redirect()->route('admin.statuses.index')
                        ->with('success', "Cleaned up {$count} expired statuses!");
    }

    /**
     * Export statuses data
     */
    public function export(Request $request)
    {
        $query = Status::with(['user'])->withCount('views');

        // Apply same filters as index
        if ($request->filled('content_type')) {
            $query->where('content_type', $request->content_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $statuses = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="statuses_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function() use ($statuses) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'User', 'Type', 'Content', 'Views', 'Status', 
                'Created At', 'Expires At'
            ]);

            foreach ($statuses as $status) {
                fputcsv($file, [
                    $status->id,
                    $status->user->name,
                    $status->content_type,
                    $status->content_type === 'text' ? $status->content : 'Media Content',
                    $status->views_count,
                    $status->expires_at > now() ? 'Active' : 'Expired',
                    $status->created_at->format('Y-m-d H:i:s'),
                    $status->expires_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get status statistics
     */
    public function statistics()
    {
        $stats = [
            'total_statuses' => Status::count(),
            'active_statuses' => Status::where('expires_at', '>', now())->count(),
            'expired_statuses' => Status::where('expires_at', '<=', now())->count(),
            'text_statuses' => Status::where('content_type', 'text')->count(),
            'image_statuses' => Status::where('content_type', 'image')->count(),
            'video_statuses' => Status::where('content_type', 'video')->count(),
            'statuses_today' => Status::whereDate('created_at', today())->count(),
            'total_views' => \DB::table('status_views')->count(),
            'most_viewed' => Status::withCount('views')->orderBy('views_count', 'desc')->first(),
            'active_users' => User::whereHas('statuses', function($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Extend status expiration
     */
    public function extend(Request $request, Status $status)
    {
        $request->validate([
            'hours' => 'required|integer|min:1|max:168'
        ]);

        $status->update([
            'expires_at' => $status->expires_at->addHours($request->hours)
        ]);

        return redirect()->back()
                        ->with('success', "Status expiration extended by {$request->hours} hours!");
    }
}
