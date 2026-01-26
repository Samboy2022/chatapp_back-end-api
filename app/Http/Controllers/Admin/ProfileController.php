<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

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
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120' // 5MB max
        ]);

        try {
            $admin->name = $request->name;
            $admin->email = $request->email;

            // Handle avatar removal
            if ($request->remove_avatar == '1') {
                if ($admin->avatar_url) {
                    // Delete from Cloudinary if it's a Cloudinary URL
                    if (str_contains($admin->avatar_url, 'cloudinary.com')) {
                        $publicId = $this->cloudinary->extractPublicId($admin->avatar_url);
                        if ($publicId) {
                            $this->cloudinary->delete($publicId, 'image');
                        }
                    }
                    $admin->avatar_url = null;
                }
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar from Cloudinary
                if ($admin->avatar_url && str_contains($admin->avatar_url, 'cloudinary.com')) {
                    $publicId = $this->cloudinary->extractPublicId($admin->avatar_url);
                    if ($publicId) {
                        $this->cloudinary->delete($publicId, 'image');
                    }
                }

                // Upload new avatar to Cloudinary
                $result = $this->cloudinary->uploadAvatar($request->file('avatar'), $admin->id);
                
                if ($result['success']) {
                    $admin->avatar_url = $result['avatar_url'];
                } else {
                    return redirect()->back()->with('error', 'Failed to upload avatar: ' . ($result['error'] ?? 'Unknown error'));
                }
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
