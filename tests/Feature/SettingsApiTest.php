<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'password' => bcrypt('password123'),
            'about' => 'Test about info',
            'last_seen_privacy' => 'everyone',
            'profile_photo_privacy' => 'everyone',
            'about_privacy' => 'everyone',
            'status_privacy' => 'everyone',
            'read_receipts_enabled' => true,
            'groups_privacy' => 'everyone'
        ]);

        // Authenticate the user
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function user_can_get_profile_settings()
    {
        $response = $this->getJson('/api/settings/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone_number' => '+1234567890',
                    'about' => 'Test about info'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_profile_name()
    {
        $response = $this->putJson('/api/settings/profile', [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'test@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function user_can_update_profile_email()
    {
        $response = $this->putJson('/api/settings/profile', [
            'email' => 'newemail@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test User',
                    'email' => 'newemail@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com'
        ]);
    }

    /** @test */
    public function user_can_update_profile_about()
    {
        $response = $this->putJson('/api/settings/profile', [
            'about' => 'Updated about information'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'about' => 'Updated about information'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'about' => 'Updated about information'
        ]);
    }

    /** @test */
    public function user_can_change_password_with_correct_current_password()
    {
        $response = $this->putJson('/api/settings/profile', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        // Verify password was changed
        $updatedUser = User::find($this->user->id);
        $this->assertTrue(password_verify('newpassword123', $updatedUser->password));
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        $response = $this->putJson('/api/settings/profile', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
    }

    /** @test */
    public function user_cannot_change_password_without_current_password()
    {
        $response = $this->putJson('/api/settings/profile', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function email_validation_fails_for_duplicate_email()
    {
        // Create another user with different email
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->putJson('/api/settings/profile', [
            'email' => 'existing@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_get_privacy_settings()
    {
        $response = $this->getJson('/api/settings/privacy');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'last_seen_privacy' => 'everyone',
                    'profile_photo_privacy' => 'everyone',
                    'about_privacy' => 'everyone',
                    'status_privacy' => 'everyone',
                    'read_receipts_enabled' => true,
                    'groups_privacy' => 'everyone'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_last_seen_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'last_seen_privacy' => 'contacts'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'last_seen_privacy' => 'contacts'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'last_seen_privacy' => 'contacts'
        ]);
    }

    /** @test */
    public function user_can_update_profile_photo_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'profile_photo_privacy' => 'nobody'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'profile_photo_privacy' => 'nobody'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'profile_photo_privacy' => 'nobody'
        ]);
    }

    /** @test */
    public function user_can_update_about_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'about_privacy' => 'contacts'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'about_privacy' => 'contacts'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'about_privacy' => 'contacts'
        ]);
    }

    /** @test */
    public function user_can_update_status_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'status_privacy' => 'close_friends'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status_privacy' => 'close_friends'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'status_privacy' => 'close_friends'
        ]);
    }

    /** @test */
    public function user_can_update_read_receipts_enabled()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'read_receipts_enabled' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'read_receipts_enabled' => false
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'read_receipts_enabled' => false
        ]);
    }

    /** @test */
    public function user_can_update_groups_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'groups_privacy' => 'contacts'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'groups_privacy' => 'contacts'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'groups_privacy' => 'contacts'
        ]);
    }

    /** @test */
    public function user_can_update_multiple_privacy_settings_at_once()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'last_seen_privacy' => 'nobody',
            'profile_photo_privacy' => 'contacts',
            'read_receipts_enabled' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'last_seen_privacy' => 'nobody',
                    'profile_photo_privacy' => 'contacts',
                    'read_receipts_enabled' => false
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'last_seen_privacy' => 'nobody',
            'profile_photo_privacy' => 'contacts',
            'read_receipts_enabled' => false
        ]);
    }

    /** @test */
    public function privacy_validation_fails_for_invalid_last_seen_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'last_seen_privacy' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_seen_privacy']);
    }

    /** @test */
    public function privacy_validation_fails_for_invalid_profile_photo_privacy()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'profile_photo_privacy' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_photo_privacy']);
    }

    /** @test */
    public function privacy_validation_blocks_unknown_fields()
    {
        $response = $this->putJson('/api/settings/privacy', [
            'unknown_field' => 'value',
            'last_seen_privacy' => 'contacts'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Unknown fields provided: unknown_field'
            ]);
    }

    /** @test */
    public function user_can_get_media_settings()
    {
        $response = $this->getJson('/api/settings/media-settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_download_photos' => true,
                    'auto_download_videos' => false,
                    'auto_download_documents' => false,
                    'media_quality' => 'high'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_media_settings()
    {
        $response = $this->putJson('/api/settings/media-settings', [
            'auto_download_photos' => false,
            'auto_download_videos' => true,
            'media_quality' => 'medium',
            'delete_media_after_days' => 30,
            'compress_images' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_download_photos' => false,
                    'auto_download_videos' => true,
                    'media_quality' => 'medium',
                    'delete_media_after_days' => 30,
                    'compress_images' => false
                ]
            ]);
    }

    /** @test */
    public function media_settings_validation_fails_for_invalid_quality()
    {
        $response = $this->putJson('/api/settings/media-settings', [
            'media_quality' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_quality']);
    }

    /** @test */
    public function user_can_get_notification_settings()
    {
        $response = $this->getJson('/api/settings/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message_notifications' => true,
                    'call_notifications' => true,
                    'notification_sound' => 'default',
                    'vibrate' => true
                ]
            ]);
    }

    /** @test */
    public function user_can_update_notification_settings()
    {
        $response = $this->putJson('/api/settings/notifications', [
            'message_notifications' => false,
            'call_notifications' => false,
            'notification_sound' => 'custom',
            'vibrate' => false,
            'notification_preview' => 'name_only',
            'high_priority_notifications' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message_notifications' => false,
                    'call_notifications' => false,
                    'notification_sound' => 'custom',
                    'vibrate' => false,
                    'notification_preview' => 'name_only',
                    'high_priority_notifications' => false
                ]
            ]);
    }

    /** @test */
    public function notification_settings_validation_fails_for_invalid_preview()
    {
        $response = $this->putJson('/api/settings/notifications', [
            'notification_preview' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notification_preview']);
    }

    /** @test */
    public function user_can_export_data()
    {
        $response = $this->getJson('/api/settings/export-data');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'profile' => [
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                        'phone_number' => '+1234567890',
                        'about' => 'Test about info'
                    ],
                    'privacy_settings' => [
                        'last_seen_privacy' => 'everyone',
                        'profile_photo_privacy' => 'everyone',
                        'about_privacy' => 'everyone',
                        'status_privacy' => 'everyone',
                        'read_receipts_enabled' => true
                    ],
                    'export_generated_at' => true // Just check that this key exists
                ]
            ]);
    }


    /** @test */
    public function account_deletion_fails_with_wrong_password()
    {
        $response = $this->deleteJson('/api/settings/delete-account', [
            'password' => 'wrongpassword',
            'confirmation' => 'DELETE_MY_ACCOUNT'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password is incorrect'
            ]);

        // User should not be deleted
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function account_deletion_fails_without_correct_confirmation()
    {
        $response = $this->deleteJson('/api/settings/delete-account', [
            'password' => 'password123',
            'confirmation' => 'wrong_confirmation'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }


    /** @test */
    public function profile_update_validation_fails_for_invalid_email()
    {
        $response = $this->putJson('/api/settings/profile', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function password_confirmation_validation_works()
    {
        $response = $this->putJson('/api/settings/profile', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }
}