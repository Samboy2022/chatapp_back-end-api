<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Display a listing of chats
     */
    public function index(Request $request)
    {
        $query = Chat::with(['participants', 'messages', 'creator'])
                    ->withCount(['participants', 'messages']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('participants', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by participant count
        if ($request->filled('min_participants')) {
            $query->having('participants_count', '>=', $request->min_participants);
        }
        if ($request->filled('max_participants')) {
            $query->having('participants_count', '<=', $request->max_participants);
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
        
        if ($sortBy === 'participants_count' || $sortBy === 'messages_count') {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $chats = $query->paginate(20)->withQueryString();

        return view('admin.chats.index', compact('chats'));
    }

    /**
     * Show the form for creating a new chat
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.chats.create', compact('users'));
    }

    /**
     * Store a newly created chat
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required_if:type,group|string|max:255',
            'type' => 'required|in:private,group',
            'description' => 'nullable|string|max:1000',
            'participants' => 'required|array|min:2',
            'participants.*' => 'exists:users,id',
            'max_participants' => 'nullable|integer|min:2|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        // For private chats, ensure exactly 2 participants
        if ($request->type === 'private' && count($request->participants) !== 2) {
            return back()->withErrors(['participants' => 'Private chats must have exactly 2 participants.']);
        }

        // For group chats, check max participants limit
        if ($request->type === 'group') {
            $maxLimit = $request->max_participants ?? 100; // Default limit
            if (count($request->participants) > $maxLimit) {
                return back()->withErrors(['participants' => "Group cannot have more than {$maxLimit} participants."]);
            }
        }

        $data = [
            'type' => $request->type,
            'name' => $request->type === 'group' ? $request->name : null,
            'description' => $request->description,
            'max_participants' => $request->type === 'group' ? ($request->max_participants ?? 100) : 2,
            'created_by' => $request->participants[0] ?? auth()->id(),
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'chat_avatar_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('chat_avatars', $filename, 'public');
            $data['avatar_url'] = Storage::disk('public')->url($path);
        }

        $chat = Chat::create($data);

        // Add participants
        foreach ($request->participants as $index => $userId) {
            DB::table('chat_participants')->insert([
                'chat_id' => $chat->id,
                'user_id' => $userId,
                'role' => $index === 0 ? 'admin' : 'member', // First participant is admin
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('admin.chats.index')
                        ->with('success', 'Chat created successfully!');
    }

    /**
     * Display the specified chat
     */
    public function show(Chat $chat)
    {
        $chat->load([
            'participants' => function($query) {
                $query->orderBy('chat_participants.role', 'desc')
                      ->orderBy('chat_participants.joined_at');
            },
            'messages' => function($query) {
                $query->with('sender')->latest()->take(50);
            },
            'creator',
            'calls' => function($query) {
                $query->with('caller')->latest()->take(10);
            }
        ]);

        // Chat statistics
        $stats = [
            'total_messages' => $chat->messages()->count(),
            'messages_today' => $chat->messages()->whereDate('created_at', today())->count(),
            'active_participants' => $chat->participants()->where('is_online', true)->count(),
            'total_calls' => $chat->calls()->count(),
            'recent_activity' => $chat->messages()->latest()->take(10)->get(),
        ];

        return view('admin.chats.show', compact('chat', 'stats'));
    }

    /**
     * Show the form for editing the specified chat
     */
    public function edit(Chat $chat)
    {
        $chat->load('participants');
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        
        return view('admin.chats.edit', compact('chat', 'users'));
    }

    /**
     * Update the specified chat
     */
    public function update(Request $request, Chat $chat)
    {
        $request->validate([
            'name' => 'required_if:type,group|string|max:255',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'nullable|integer|min:2|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        $data = [
            'name' => $chat->type === 'group' ? $request->name : $chat->name,
            'description' => $request->description,
            'max_participants' => $chat->type === 'group' ? ($request->max_participants ?? 100) : 2,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($chat->avatar_url) {
                $oldPath = str_replace('/storage/', '', parse_url($chat->avatar_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('avatar');
            $filename = 'chat_avatar_' . $chat->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('chat_avatars', $filename, 'public');
            $data['avatar_url'] = Storage::disk('public')->url($path);
        }

        $chat->update($data);

        return redirect()->route('admin.chats.show', $chat)
                        ->with('success', 'Chat updated successfully!');
    }

    /**
     * Remove the specified chat
     */
    public function destroy(Chat $chat)
    {
        // Delete avatar if exists
        if ($chat->avatar_url) {
            $oldPath = str_replace('/storage/', '', parse_url($chat->avatar_url, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }

        $chat->delete();

        return redirect()->route('admin.chats.index')
                        ->with('success', 'Chat deleted successfully!');
    }

    /**
     * Add participant to chat
     */
    public function addParticipant(Request $request, Chat $chat)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:member,admin'
        ]);

        // Check if user is already a participant
        if ($chat->participants()->where('user_id', $request->user_id)->exists()) {
            return back()->withErrors(['error' => 'User is already a participant in this chat.']);
        }

        // Check maximum participants limit
        if ($chat->participants()->count() >= $chat->max_participants) {
            return back()->withErrors(['error' => 'Chat has reached maximum participants limit.']);
        }

        DB::table('chat_participants')->insert([
            'chat_id' => $chat->id,
            'user_id' => $request->user_id,
            'role' => $request->role,
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Participant added successfully!');
    }

    /**
     * Remove participant from chat
     */
    public function removeParticipant(Request $request, Chat $chat, User $user)
    {
        // Don't allow removing the last admin from group chats
        if ($chat->type === 'group') {
            $participant = $chat->participants()->where('user_id', $user->id)->first();
            if ($participant && $participant->pivot->role === 'admin') {
                $adminCount = $chat->participants()->wherePivot('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return back()->withErrors(['error' => 'Cannot remove the last admin from a group chat.']);
                }
            }
        }

        $chat->participants()->detach($user->id);

        return back()->with('success', 'Participant removed successfully!');
    }

    /**
     * Toggle chat active status
     */
    public function toggleActive(Chat $chat)
    {
        $chat->update(['is_active' => !($chat->is_active ?? true)]);
        
        $status = $chat->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Chat has been {$status} successfully!");
    }

    /**
     * Export chats data
     */
    public function export(Request $request)
    {
        $chats = Chat::with(['participants', 'creator'])
                    ->withCount(['participants', 'messages'])
                    ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="chats_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function() use ($chats) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Type', 'Name', 'Description', 'Participants Count', 
                'Messages Count', 'Max Participants', 'Creator', 'Created At'
            ]);

            foreach ($chats as $chat) {
                fputcsv($file, [
                    $chat->id,
                    $chat->type,
                    $chat->name,
                    $chat->description,
                    $chat->participants_count,
                    $chat->messages_count,
                    $chat->max_participants ?? 'N/A',
                    $chat->creator ? $chat->creator->name : 'N/A',
                    $chat->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get chat statistics
     */
    public function statistics()
    {
        $stats = [
            'total_chats' => Chat::count(),
            'private_chats' => Chat::where('type', 'private')->count(),
            'group_chats' => Chat::where('type', 'group')->count(),
            'active_chats' => Chat::whereHas('messages', function($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })->count(),
            'average_participants' => Chat::withCount('participants')->get()->avg('participants_count'),
            'largest_group' => Chat::where('type', 'group')->withCount('participants')->orderBy('participants_count', 'desc')->first(),
        ];

        return response()->json($stats);
    }
}
