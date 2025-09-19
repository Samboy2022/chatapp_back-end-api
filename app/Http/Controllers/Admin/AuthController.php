<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLogin()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials provided.'
            ])->withInput();
        }

        // Check if user is admin (you can add role field or use email check)
        if (!$this->isAdmin($user)) {
            return back()->withErrors([
                'email' => 'You do not have admin privileges.'
            ])->withInput();
        }

        // Create admin session
        session([
            'admin_logged_in' => true,
            'admin_user_id' => $user->id,
            'admin_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ]
        ]);

        return redirect()->route('admin.dashboard')
                        ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        session()->forget(['admin_logged_in', 'admin_user_id', 'admin_user']);
        session()->flush();
        
        return redirect()->route('admin.login')
                        ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Check if user is admin
     */
    private function isAdmin(User $user): bool
    {
        // Method 1: Check specific admin emails
        $adminEmails = [
            'admin@chatapp.com',
            'superadmin@chatapp.com',
            'admin@example.com'
        ];

        if (in_array($user->email, $adminEmails)) {
            return true;
        }

        // Method 2: Check if user has admin role (if you add role field)
        // return $user->role === 'admin';

        // Method 3: Check if user is first user (ID = 1)
        if ($user->id === 1) {
            return true;
        }

        return false;
    }

    /**
     * Get current admin user
     */
    public static function getCurrentAdmin()
    {
        if (session('admin_logged_in')) {
            return session('admin_user');
        }
        return null;
    }
}
