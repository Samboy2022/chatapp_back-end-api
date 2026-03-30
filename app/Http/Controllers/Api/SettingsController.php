<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Get user profile settings
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'country_code' => $user->country_code,
                'avatar_url' => $user->avatar_url,
                'about' => $user->about,
                'last_seen_at' => $user->last_seen_at,
                'is_online' => $user->is_online,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Profile retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile settings
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'about' => 'nullable|string|max:500',
                'email' => 'nullable|email|unique:users,email,' . Auth::id(),
                'current_password' => 'nullable|string|required_with:new_password',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $updateData = [];

            // Update name if provided
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            // Update about if provided
            if ($request->has('about')) {
                $updateData['about'] = $request->about;
            }

            // Update email if provided
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }

            // Update password if provided
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

            return response()->json([
                'success' => true,
                'data' => $user->only(['id', 'name', 'email', 'phone_number', 'about', 'avatar_url']),
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get privacy settings
     */
    public function getPrivacy(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $privacySettings = [
                'last_seen_privacy' => $user->last_seen_privacy,
                'profile_photo_privacy' => $user->profile_photo_privacy,
                'about_privacy' => $user->about_privacy,
                'status_privacy' => $user->status_privacy,
                'read_receipts_enabled' => $user->read_receipts_enabled,
                'groups_privacy' => $user->groups_privacy
            ];

            return response()->json([
                'success' => true,
                'data' => $privacySettings,
                'message' => 'Privacy settings retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving privacy settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacy(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'last_seen_privacy' => 'nullable|string|in:everyone,contacts,nobody',
                'profile_photo_privacy' => 'nullable|string|in:everyone,contacts,nobody',
                'about_privacy' => 'nullable|string|in:everyone,contacts,nobody',
                'status_privacy' => 'nullable|string|in:everyone,contacts,close_friends',
                'read_receipts_enabled' => 'nullable|boolean',
                'groups_privacy' => 'nullable|string|in:everyone,contacts'
            ], [
                'last_seen_privacy.in' => 'Last seen privacy must be one of: everyone, contacts, nobody',
                'profile_photo_privacy.in' => 'Profile photo privacy must be one of: everyone, contacts, nobody',
                'about_privacy.in' => 'About privacy must be one of: everyone, contacts, nobody',
                'status_privacy.in' => 'Status privacy must be one of: everyone, contacts, close_friends',
                'read_receipts_enabled.boolean' => 'Read receipts must be true or false',
                'groups_privacy.in' => 'Groups privacy must be one of: everyone, contacts'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for unknown fields
            $allowedFields = [
                'last_seen_privacy', 'profile_photo_privacy', 'about_privacy',
                'status_privacy', 'read_receipts_enabled', 'groups_privacy'
            ];

            $unknownFields = array_diff(array_keys($request->all()), $allowedFields);
            if (!empty($unknownFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown fields provided: ' . implode(', ', $unknownFields),
                    'errors' => ['unknown_fields' => $unknownFields]
                ], 422);
            }

            $user = Auth::user();
            $updateData = [];

            // Update privacy settings if provided
            if ($request->has('last_seen_privacy')) {
                $updateData['last_seen_privacy'] = $request->last_seen_privacy;
            }

            if ($request->has('profile_photo_privacy')) {
                $updateData['profile_photo_privacy'] = $request->profile_photo_privacy;
            }

            if ($request->has('about_privacy')) {
                $updateData['about_privacy'] = $request->about_privacy;
            }

            if ($request->has('status_privacy')) {
                $updateData['status_privacy'] = $request->status_privacy;
            }

            if ($request->has('read_receipts_enabled')) {
                $updateData['read_receipts_enabled'] = $request->read_receipts_enabled;
            }

            if ($request->has('groups_privacy')) {
                $updateData['groups_privacy'] = $request->groups_privacy;
            }

            // Update user privacy settings
            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return response()->json([
                'success' => true,
                'data' => $user->only([
                    'last_seen_privacy', 
                    'profile_photo_privacy', 
                    'about_privacy', 
                    'status_privacy', 
                    'read_receipts_enabled',
                    'groups_privacy'
                ]),
                'message' => 'Privacy settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating privacy settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media and storage settings
     */
    public function getMediaSettings(): JsonResponse
    {
        try {
            // These could be stored in user preferences or configuration
            $mediaSettings = [
                'auto_download_photos' => true,
                'auto_download_videos' => false,
                'auto_download_documents' => false,
                'auto_download_on_mobile' => false,
                'auto_download_on_wifi' => true,
                'media_quality' => 'high', // high, medium, low
                'voice_message_playback_speed' => 1.0,
                'delete_media_after_days' => 30,
                'compress_images' => true,
                'compress_videos' => true,
                'save_to_gallery' => true
            ];

            return response()->json([
                'success' => true,
                'data' => $mediaSettings,
                'message' => 'Media settings retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving media settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update media and storage settings
     */
    public function updateMediaSettings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'auto_download_photos' => 'nullable|boolean',
                'auto_download_videos' => 'nullable|boolean',
                'auto_download_documents' => 'nullable|boolean',
                'auto_download_on_mobile' => 'nullable|boolean',
                'auto_download_on_wifi' => 'nullable|boolean',
                'media_quality' => 'nullable|string|in:high,medium,low',
                'voice_message_playback_speed' => 'nullable|numeric|min:0.5|max:2.0',
                'delete_media_after_days' => 'nullable|integer|min:1|max:365',
                'compress_images' => 'nullable|boolean',
                'compress_videos' => 'nullable|boolean',
                'save_to_gallery' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // In a real application, you would save these to a user_preferences table
            // For now, we'll just return the updated settings
            $updatedSettings = $request->only([
                'auto_download_photos',
                'auto_download_videos',
                'auto_download_documents',
                'auto_download_on_mobile',
                'auto_download_on_wifi',
                'media_quality',
                'voice_message_playback_speed',
                'delete_media_after_days',
                'compress_images',
                'compress_videos',
                'save_to_gallery'
            ]);

            return response()->json([
                'success' => true,
                'data' => $updatedSettings,
                'message' => 'Media settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating media settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings(): JsonResponse
    {
        try {
            // These would typically be stored in user preferences
            $notificationSettings = [
                'message_notifications' => true,
                'call_notifications' => true,
                'status_notifications' => true,
                'group_notifications' => true,
                'notification_sound' => 'default',
                'vibrate' => true,
                'notification_light' => true,
                'in_app_sounds' => true,
                'in_app_vibrate' => true,
                'notification_preview' => 'name_and_message', // name_only, name_and_message, none
                'high_priority_notifications' => true
            ];

            return response()->json([
                'success' => true,
                'data' => $notificationSettings,
                'message' => 'Notification settings retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notification settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_notifications' => 'nullable|boolean',
                'call_notifications' => 'nullable|boolean',
                'status_notifications' => 'nullable|boolean',
                'group_notifications' => 'nullable|boolean',
                'notification_sound' => 'nullable|string',
                'vibrate' => 'nullable|boolean',
                'notification_light' => 'nullable|boolean',
                'in_app_sounds' => 'nullable|boolean',
                'in_app_vibrate' => 'nullable|boolean',
                'notification_preview' => 'nullable|string|in:name_only,name_and_message,none',
                'high_priority_notifications' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updatedSettings = $request->only([
                'message_notifications',
                'call_notifications',
                'status_notifications',
                'group_notifications',
                'notification_sound',
                'vibrate',
                'notification_light',
                'in_app_sounds',
                'in_app_vibrate',
                'notification_preview',
                'high_priority_notifications'
            ]);

            return response()->json([
                'success' => true,
                'data' => $updatedSettings,
                'message' => 'Notification settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating notification settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect'
                ], 422);
            }

            // Here you would typically:
            // 1. Delete or anonymize user data
            // 2. Remove from all chats
            // 3. Delete media files
            // 4. Clean up relationships
            
            // Revoke all tokens first before marking as deleted
            $user->tokens()->delete();

            // Mark the account as deleted
            $user->update([
                'deleted_at' => now(),
                'email' => $user->email . '_deleted_' . time(),
                'phone_number' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export user data
     */
    public function exportData(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // In a real application, this would generate a comprehensive data export
            $userData = [
                'profile' => $user->only([
                    'name', 'email', 'phone_number', 'about', 'created_at'
                ]),
                'privacy_settings' => $user->only([
                    'last_seen_privacy', 'profile_photo_privacy', 
                    'about_privacy', 'status_privacy', 'read_receipts_enabled'
                ]),
                'export_generated_at' => now(),
                'note' => 'This is a sample export. In production, this would include messages, media, and other user data.'
            ];

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Data export generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting data: ' . $e->getMessage()
            ], 500);
        }
    }
}
