<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'blocked') {
                $query->whereNotNull('deleted_at');
            } elseif ($request->status === 'online') {
                $query->where('is_online', true);
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

        $users = $query->withCount(['messages', 'chats', 'statuses'])->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|unique:users',
            'country_code' => 'nullable|string|max:5',
            'password' => 'required|string|min:8|confirmed',
            'about' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        $data = $request->only([
            'name', 'email', 'phone_number', 'country_code', 'about'
        ]);
        
        $data['password'] = Hash::make($request->password);
        $data['email_verified_at'] = now();
        $data['phone_verified_at'] = now();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');
            $data['avatar_url'] = Storage::disk('public')->url($path);
        }

        $user = User::create($data);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load([
            'chats' => function($query) {
                $query->latest()->take(10);
            },
            'messages' => function($query) {
                $query->latest()->take(20);
            },
            'sentCalls' => function($query) {
                $query->latest()->take(10);
            },
            'statuses' => function($query) {
                $query->latest()->take(10);
            }
        ]);

        // Get user statistics
        $stats = [
            'total_chats' => $user->chats()->count(),
            'private_chats' => $user->chats()->where('chats.type', 'private')->count(),
            'group_chats' => $user->chats()->where('chats.type', 'group')->count(),
            'total_messages' => $user->messages()->count(),
            'messages_today' => $user->messages()->whereDate('created_at', today())->count(),
            'total_calls' => $user->sentCalls()->count() + $user->receivedCalls()->count(),
            'calls_made' => $user->sentCalls()->count(),
            'calls_received' => $user->receivedCalls()->count(),
            'total_statuses' => $user->statuses()->count(),
            'active_statuses' => $user->statuses()->where('expires_at', '>', now())->count(),
            'contacts_count' => Contact::where('user_id', $user->id)->count(),
            'blocked_contacts' => Contact::where('user_id', $user->id)->where('is_blocked', true)->count()
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'country_code' => 'nullable|string|max:5',
            'password' => 'nullable|string|min:8|confirmed',
            'about' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_online' => 'boolean',
            'last_seen_privacy' => 'in:everyone,contacts,nobody',
            'profile_photo_privacy' => 'in:everyone,contacts,nobody',
            'about_privacy' => 'in:everyone,contacts,nobody',
            'status_privacy' => 'in:everyone,contacts,close_friends',
            'read_receipts_enabled' => 'boolean',
            'groups_privacy' => 'in:everyone,contacts'
        ]);

        $data = $request->only([
            'name', 'email', 'phone_number', 'country_code', 'about',
            'is_online', 'last_seen_privacy', 'profile_photo_privacy',
            'about_privacy', 'status_privacy', 'read_receipts_enabled', 'groups_privacy'
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar_url) {
                $oldPath = str_replace('/storage/', '', parse_url($user->avatar_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $data['avatar_url'] = Storage::disk('public')->url($path);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)
                        ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Instead of hard delete, we'll soft delete by setting deleted_at
        $user->update([
            'deleted_at' => now(),
            'email' => $user->email . '_deleted_' . time(),
            'phone_number' => null
        ]);

        // Revoke all API tokens
        $user->tokens()->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User account has been deactivated successfully!');
    }

    /**
     * Block/Unblock user
     */
    public function toggleBlock($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->deleted_at) {
            // Unblock user
            $user->update(['deleted_at' => null]);
            $message = 'User has been unblocked successfully!';
        } else {
            // Block user
            $user->update(['deleted_at' => now()]);
            $user->tokens()->delete(); // Revoke all tokens
            $message = 'User has been blocked successfully!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->get();

        $csvContent = "ID,Name,Email,Phone,Country Code,Created At,Last Seen,Status\n";
        
        foreach ($users as $user) {
            $status = $user->deleted_at ? 'Blocked' : ($user->is_online ? 'Online' : 'Offline');
            $csvContent .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s\n",
                $user->id,
                '"' . str_replace('"', '""', $user->name) . '"',
                $user->email,
                $user->phone_number ?? '',
                $user->country_code ?? '',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->last_seen_at ? $user->last_seen_at->format('Y-m-d H:i:s') : '',
                $status
            );
        }

        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $newPassword = 'password123'; // In production, generate a random password
        $user->update(['password' => Hash::make($newPassword)]);

        // In production, you would send this password via email
        return redirect()->back()
                        ->with('success', "Password reset successfully! New password: {$newPassword}");
    }
}
