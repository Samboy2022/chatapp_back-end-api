<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Display a listing of messages
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'chat'])
                       ->select('messages.*');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('sender', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('chat', function($query) use ($search) {
                      $query->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by message type
        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        // Filter by chat
        if ($request->filled('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }

        // Filter by sender
        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter deleted messages
        if ($request->filled('show_deleted') && $request->show_deleted === '1') {
            $query->where('is_deleted', true);
        } else {
            $query->where('is_deleted', false);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $messages = $query->paginate(50)->withQueryString();

        // Get filter options
        $chats = Chat::select('id', 'name', 'type')->orderBy('name')->get();
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.messages.index', compact('messages', 'chats', 'users'));
    }

    /**
     * Show the form for creating a new message
     */
    public function create()
    {
        $chats = Chat::with('participants')->orderBy('name')->get();
        return view('admin.messages.create', compact('chats'));
    }

    /**
     * Store a newly created message
     */
    public function store(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'sender_id' => 'required|exists:users,id',
            'message_type' => 'required|in:text,image,video,audio,document,location,contact',
            'content' => 'required_if:message_type,text|nullable|string',
            'media' => 'nullable|file|max:50000', // 50MB max
            'latitude' => 'required_if:message_type,location|nullable|numeric',
            'longitude' => 'required_if:message_type,location|nullable|numeric',
            'location_name' => 'nullable|string|max:255',
        ]);

        $data = [
            'chat_id' => $request->chat_id,
            'sender_id' => $request->sender_id,
            'message_type' => $request->message_type,
            'content' => $request->content,
            'status' => 'sent',
            'sent_at' => now(),
        ];

        // Handle media upload
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = 'message_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('messages', $filename, 'public');
            
            $data['media_url'] = Storage::disk('public')->url($path);
            $data['media_size'] = $file->getSize();
            $data['media_mime_type'] = $file->getMimeType();
            $data['file_name'] = $file->getClientOriginalName();
        }

        // Handle location data
        if ($request->message_type === 'location') {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $data['location_name'] = $request->location_name;
        }

        Message::create($data);

        return redirect()->route('admin.messages.index')
                        ->with('success', 'Message created successfully!');
    }

    /**
     * Display the specified message
     */
    public function show(Message $message)
    {
        $message->load(['sender', 'chat.participants', 'reactions.user']);
        
        return view('admin.messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified message
     */
    public function edit(Message $message)
    {
        $chats = Chat::with('participants')->orderBy('name')->get();
        return view('admin.messages.edit', compact('message', 'chats'));
    }

    /**
     * Update the specified message
     */
    public function update(Request $request, Message $message)
    {
        $request->validate([
            'content' => 'required_if:message_type,text|nullable|string',
            'status' => 'required|in:sending,sent,delivered,read',
        ]);

        $message->update([
            'content' => $request->content,
            'status' => $request->status,
            'edited_at' => now(),
        ]);

        return redirect()->route('admin.messages.show', $message)
                        ->with('success', 'Message updated successfully!');
    }

    /**
     * Remove the specified message (soft delete)
     */
    public function destroy(Message $message)
    {
        $message->update(['is_deleted' => true]);

        return redirect()->route('admin.messages.index')
                        ->with('success', 'Message deleted successfully!');
    }

    /**
     * Permanently delete the message
     */
    public function forceDelete(Message $message)
    {
        // Delete associated media
        if ($message->media_url) {
            $path = str_replace('/storage/', '', parse_url($message->media_url, PHP_URL_PATH));
            Storage::disk('public')->delete($path);
        }

        $message->forceDelete();

        return redirect()->route('admin.messages.index')
                        ->with('success', 'Message permanently deleted!');
    }

    /**
     * Restore a soft deleted message
     */
    public function restore($id)
    {
        $message = Message::withTrashed()->findOrFail($id);
        $message->update(['is_deleted' => false]);

        return redirect()->route('admin.messages.index')
                        ->with('success', 'Message restored successfully!');
    }

    /**
     * Export messages data
     */
    public function export(Request $request)
    {
        $query = Message::with(['sender', 'chat']);

        // Apply same filters as index
        if ($request->filled('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }
        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="messages_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Chat', 'Sender', 'Type', 'Content', 'Status', 
                'Media URL', 'File Size', 'Created At'
            ]);

            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->chat->name ?? 'Private Chat',
                    $message->sender->name,
                    $message->message_type,
                    $message->content,
                    $message->status,
                    $message->media_url ?? '',
                    $message->media_size ?? '',
                    $message->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get message statistics
     */
    public function statistics()
    {
        $stats = [
            'total_messages' => Message::count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'text_messages' => Message::where('message_type', 'text')->count(),
            'media_messages' => Message::whereIn('message_type', ['image', 'video', 'audio', 'document'])->count(),
            'deleted_messages' => Message::where('is_deleted', true)->count(),
            'unread_messages' => Message::where('status', '!=', 'read')->count(),
            'popular_chats' => Chat::withCount('messages')->orderBy('messages_count', 'desc')->take(5)->get(),
            'active_users' => User::whereHas('messages', function($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Moderate message content
     */
    public function moderate(Request $request, Message $message)
    {
        $request->validate([
            'action' => 'required|in:approve,flag,delete',
            'reason' => 'nullable|string|max:500'
        ]);

        switch ($request->action) {
            case 'approve':
                $message->update(['is_flagged' => false]);
                break;
            case 'flag':
                $message->update(['is_flagged' => true, 'flag_reason' => $request->reason]);
                break;
            case 'delete':
                $message->update(['is_deleted' => true, 'deletion_reason' => $request->reason]);
                break;
        }

        return redirect()->back()->with('success', 'Message moderated successfully!');
    }
}
