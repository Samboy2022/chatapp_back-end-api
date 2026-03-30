<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => Hash::make('password123'),
            'about' => 'Hey there! I am using ChatApp.',
        ]);

        // Authenticate user
        Sanctum::actingAs($this->user);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function user_can_get_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/settings/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'country_code',
                    'avatar_url',
                    'about',
                    'is_online',
                    'created_at',
                    'updated_at'
                ],
                'message'
            ]);

        $this->assertEquals('John Doe', $response->json('data.name'));
        $this->assertEquals('john@example.com', $response->json('data.email'));
    }

    /** @test */
    public function user_can_update_profile_name()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'name' => 'Jane Smith'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Jane Smith'
        ]);
    }

    /** @test */
    public function user_can_update_profile_about()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/profile', [
            'about' => 'Living my best life!'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'about' => 'Living my best life!'
        ]);
    }

    /** @test */
    public function user_can_update_email()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'email' => 'newemail@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com'
        ]);
    }

    /** @test */
    public function user_cannot_update_email_to_existing_email()
    {
        // Create another user with an email
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'email' => 'existing@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_change_password_with_correct_current_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        // Verify new password works
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->user->password));
    }

    /** @test */
    public function user_cannot_change_password_with_incorrect_current_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect'
            ]);
    }

    /** @test */
    public function user_can_upload_avatar()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/media/upload/avatar', [
            'avatar' => $file
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Avatar uploaded successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'avatar_url',
                    'thumbnail_url'
                ],
                'message'
            ]);
    }

    /** @test */
    public function avatar_upload_validates_file_type()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/media/upload/avatar', [
            'avatar' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function avatar_upload_validates_file_size()
    {
        Storage::fake('public');

        // Create file larger than 5MB
        $file = UploadedFile::fake()->image('avatar.jpg')->size(6000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/media/upload/avatar', [
            'avatar' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function user_can_get_privacy_settings()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/settings/privacy');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Privacy settings retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'last_seen_privacy',
                    'profile_photo_privacy',
                    'about_privacy',
                    'status_privacy',
                    'read_receipts_enabled',
                    'groups_privacy'
                ],
                'message'
            ]);
    }

    /** @test */
    public function user_can_update_privacy_settings()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/privacy', [
            'last_seen_privacy' => 'contacts',
            'profile_photo_privacy' => 'nobody',
            'about_privacy' => 'everyone',
            'read_receipts_enabled' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Privacy settings updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'last_seen_privacy' => 'contacts',
            'profile_photo_privacy' => 'nobody',
            'about_privacy' => 'everyone',
            'read_receipts_enabled' => false
        ]);
    }

    /** @test */
    public function privacy_settings_validate_allowed_values()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/privacy', [
            'last_seen_privacy' => 'invalid_value'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_seen_privacy']);
    }

    /** @test */
    public function user_can_delete_account_with_correct_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/settings/delete-account', [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        // Verify account is soft deleted
        $this->user->refresh();
        $this->assertNotNull($this->user->deleted_at);
    }

    /** @test */
    public function user_cannot_delete_account_with_incorrect_password()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/settings/delete-account', [
            'password' => 'wrongpassword',
            'confirmation' => 'DELETE_MY_ACCOUNT'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password is incorrect'
            ]);
    }

    /** @test */
    public function user_cannot_delete_account_without_confirmation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/settings/delete-account', [
            'password' => 'password123',
            'confirmation' => 'WRONG_CONFIRMATION'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }

    /** @test */
    public function user_can_export_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/settings/export-data');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data export generated successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile',
                    'privacy_settings',
                    'export_generated_at'
                ],
                'message'
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_profile_endpoints()
    {
        $response = $this->getJson('/api/settings/profile');
        $response->assertStatus(401);

        $response = $this->putJson('/api/settings/profile', ['name' => 'Test']);
        $response->assertStatus(401);

        $response = $this->getJson('/api/settings/privacy');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/settings/delete-account');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_update_multiple_profile_fields_at_once()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'name' => 'Updated Name',
            'about' => 'Updated about text',
            'email' => 'updated@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'about' => 'Updated about text',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function profile_update_validates_field_lengths()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/profile', [
            'about' => str_repeat('a', 501) // Exceeds 500 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['about']);
    }

    /** @test */
    public function user_can_get_notification_settings()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/settings/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification settings retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'message_notifications',
                    'call_notifications',
                    'status_notifications',
                    'group_notifications',
                    'notification_sound',
                    'vibrate'
                ],
                'message'
            ]);
    }

    /** @test */
    public function user_can_update_notification_settings()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/notifications', [
            'message_notifications' => false,
            'call_notifications' => true,
            'vibrate' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification settings updated successfully'
            ]);
    }

    /** @test */
    public function password_change_requires_confirmation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function password_must_meet_minimum_length()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/auth/profile', [
            'current_password' => 'password123',
            'new_password' => 'short',
            'new_password_confirmation' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }
}
