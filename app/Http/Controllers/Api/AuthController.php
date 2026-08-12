<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        // Canonicalise before validating, so the `unique` rule compares the
        // same form we're about to store. Without this, one person could
        // register twice — once as 07026591356 and once as +2347026591356.
        $normalizedPhone = PhoneNumber::normalize(
            $request->input('phone_number'),
            $request->input('country_code')
        );

        if ($normalizedPhone === null) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => ['phone_number' => ['Please enter a valid phone number.']],
            ], 422);
        }

        $request->merge([
            'phone_number' => $normalizedPhone,
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'country_code' => 'required|string|max:5',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'country_code' => $request->country_code,
                'password' => Hash::make($request->password),
            ]);

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // Can be email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Matches an email, or a phone number in any of the forms a user
            // might type it: 07026591356 and +2347026591356 both land here.
            $user = User::findByLogin($request->login);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Update online status
            $user->updateOnlineStatus(true);

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Update offline status
            $request->user()->updateOnlineStatus(false);
            
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['contacts.contactUser']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // Same canonicalisation as registration — otherwise editing a profile
        // could quietly reintroduce a non-normalised number that then fails to
        // log in.
        if ($request->has('phone_number')) {
            $normalized = PhoneNumber::normalize(
                $request->input('phone_number'),
                $request->input('country_code') ?? $request->user()->country_code
            );

            if ($normalized === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => ['phone_number' => ['Please enter a valid phone number.']],
                ], 422);
            }

            $request->merge(['phone_number' => $normalized]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,' . $request->user()->id,
            'country_code' => 'sometimes|string|max:5',
            'about' => 'sometimes|string|max:500',
            'avatar_url' => 'sometimes|url|max:500',
            'current_password' => 'sometimes|required_with:new_password|string',
            'new_password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $updateData = [];

            // Update basic profile fields
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->has('phone_number')) {
                $updateData['phone_number'] = $request->phone_number;
            }

            if ($request->has('country_code')) {
                $updateData['country_code'] = $request->country_code;
            }

            if ($request->has('about')) {
                $updateData['about'] = $request->about;
            }

            if ($request->has('avatar_url')) {
                $updateData['avatar_url'] = $request->avatar_url;
            }

            // Handle password change
            if ($request->filled('new_password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
                
                $updateData['password'] = Hash::make($request->new_password);
            }

            // Update user
            if (!empty($updateData)) {
                $user->update($updateData);
            }

            // Refresh user data
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user->makeHidden(['password', 'remember_token'])
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'privacy_last_seen' => 'sometimes|in:everyone,contacts,nobody',
            'privacy_profile_photo' => 'sometimes|in:everyone,contacts,nobody',
            'privacy_about' => 'sometimes|in:everyone,contacts,nobody',
            'read_receipts' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $user->update($request->only([
                'privacy_last_seen',
                'privacy_profile_photo', 
                'privacy_about',
                'read_receipts'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Privacy settings updated successfully',
                'data' => [
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            // Delete old token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $request->user()->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user FCM/device token for push notifications
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:512',
            'device_type' => 'sometimes|string|in:android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $request->user()->update([
                'fcm_token' => $request->fcm_token,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device token updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a test push to the signed-in user.
     *
     * Useful for confirming a device is actually reachable — it exercises the
     * same path a real incoming call uses.
     */
    public function testFcmNotification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $push = app(\App\Services\OneSignalService::class);

            if (!$push->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OneSignal is not configured. Add the REST API key in admin settings.',
                ], 503);
            }

            $sent = $push->sendCallNotification((string) $user->id, [
                'type' => 'incoming_call',
                'call_id' => 'test_' . time(),
                'channel' => 'test_channel',
                'caller_name' => 'Test Notification',
                'caller_avatar' => '',
                'call_type' => 'audio',
                'receiver_token' => 'test_token',
                'app_id' => 'test_app_id',
            ]);

            return response()->json([
                'success' => $sent,
                'message' => $sent
                    ? 'Test notification sent'
                    : 'No subscribed device for this account. Open the app and allow notifications, then try again.',
            ], $sent ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
            ], 500);
        }
    }
}
