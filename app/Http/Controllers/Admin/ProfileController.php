<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function index()
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || !isset($adminUser['id'])) {
            return redirect()->route('admin.login')->with('error', 'Please login to access your profile.');
        }

        $admin = User::find($adminUser['id']);
        
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Admin user not found.');
        }

        return view('admin.profile.index', compact('admin'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || !isset($adminUser['id'])) {
            return redirect()->route('admin.login')->with('error', 'Please login to update your profile.');
        }

        $admin = User::find($adminUser['id']);
        
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Admin user not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048'
        ]);

        try {
            $admin->name = $request->name;
            $admin->email = $request->email;

            // Handle avatar removal
            if ($request->remove_avatar == '1') {
                if ($admin->avatar_url) {
                    $oldPath = str_replace('/storage/', '', parse_url($admin->avatar_url, PHP_URL_PATH));
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $admin->avatar_url = null;
                }
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($admin->avatar_url) {
                    $oldPath = str_replace('/storage/', '', parse_url($admin->avatar_url, PHP_URL_PATH));
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $file = $request->file('avatar');
                $filename = 'admin_avatar_' . $admin->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'public');
                $admin->avatar_url = Storage::disk('public')->url($path);
            }

            $admin->save();

            // Update session data
            session(['admin_user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar_url' => $admin->avatar_url,
                'is_admin' => $admin->is_admin
            ]]);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || !isset($adminUser['id'])) {
            return redirect()->route('admin.login')->with('error', 'Please login to update your password.');
        }

        $admin = User::find($adminUser['id']);
        
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Admin user not found.');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        try {
            $admin->password = Hash::make($request->password);
            $admin->save();

            return redirect()->back()->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
}
