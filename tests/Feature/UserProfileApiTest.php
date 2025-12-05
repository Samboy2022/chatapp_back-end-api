<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class UserProfileApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $baseUrl = '/api';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate user
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => Hash::make('password123'),
            'about' => 'Hello, I am using this app!',
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function test_authenticated_user_can_get_their_profile()
    {
        $response = $this->getJson("{$this->baseUrl}/auth/user");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'country_code',
                        'avatar_url',
                        'about',
                        'is_online',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_can_update_profile_name()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'name' => 'Jane Smith',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'Jane Smith',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Jane Smith',
        ]);
    }

    /** @test */
    public function test_can_update_profile_email()
    {
        $newEmail = 'newemail@example.com';

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'email' => $newEmail,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => $newEmail,
        ]);
    }

    /** @test */
    public function test_can_update_profile_phone_number()
    {
        $newPhone = '+9876543210';

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'phone_number' => $newPhone,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'phone_number' => $newPhone,
        ]);
    }

    /** @test */
    public function test_can_update_profile_about()
    {
        $newAbout = 'This is my new status message!';

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'about' => $newAbout,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'about' => $newAbout,
        ]);
    }

    /** @test */
    public function test_can_update_profile_avatar_url()
    {
        $avatarUrl = 'https://res.cloudinary.com/test/image/upload/avatar.jpg';

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'avatar_url' => $avatarUrl,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar_url' => $avatarUrl,
        ]);
    }

    /** @test */
    public function test_can_update_multiple_profile_fields_at_once()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'name' => 'Updated Name',
            'about' => 'Updated about',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'about' => 'Updated about',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function test_can_change_password_with_correct_current_password()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        // Verify new password works
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->user->password));
    }

    /** @test */
    public function test_cannot_change_password_with_incorrect_current_password()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect',
            ]);

        // Verify old password still works
        $this->user->refresh();
        $this->assertTrue(Hash::check('password123', $this->user->password));
    }

    /** @test */
    public function test_password_change_requires_confirmation()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            // Missing new_password_confirmation
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function test_new_password_must_be_at_least_8_characters()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'current_password' => 'password123',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function test_email_must_be_unique()
    {
        $otherUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function test_phone_number_must_be_unique()
    {
        $otherUser = User::factory()->create([
            'phone_number' => '+9999999999',
        ]);

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'phone_number' => '+9999999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function test_email_must_be_valid_format()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'email' => 'invalid-email-format',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function test_name_cannot_exceed_255_characters()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function test_about_cannot_exceed_500_characters()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'about' => str_repeat('a', 501),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['about']);
    }

    /** @test */
    public function test_avatar_url_must_be_valid_url()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'avatar_url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar_url']);
    }

    /** @test */
    public function test_profile_update_requires_authentication()
    {
        Sanctum::actingAs(null);

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_can_get_profile_from_settings_endpoint()
    {
        $response = $this->getJson("{$this->baseUrl}/settings/profile");

        $response->assertStatus(200)
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
                    'last_seen_at',
                    'is_online',
                    'created_at',
                    'updated_at',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully',
            ]);
    }

    /** @test */
    public function test_can_update_profile_from_settings_endpoint()
    {
        $response = $this->putJson("{$this->baseUrl}/settings/profile", [
            'name' => 'Settings Updated Name',
            'about' => 'Settings updated about',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Settings Updated Name',
            'about' => 'Settings updated about',
        ]);
    }

    /** @test */
    public function test_can_delete_account_with_correct_password()
    {
        $response = $this->deleteJson("{$this->baseUrl}/settings/delete-account", [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);

        // Check that user is soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_cannot_delete_account_with_incorrect_password()
    {
        $response = $this->deleteJson("{$this->baseUrl}/settings/delete-account", [
            'password' => 'wrongpassword',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Password is incorrect',
            ]);

        // Check that user is NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function test_account_deletion_requires_confirmation_text()
    {
        $response = $this->deleteJson("{$this->baseUrl}/settings/delete-account", [
            'password' => 'password123',
            'confirmation' => 'wrong_confirmation',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirmation']);
    }

    /** @test */
    public function test_account_deletion_requires_password()
    {
        $response = $this->deleteJson("{$this->baseUrl}/settings/delete-account", [
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function test_can_export_user_data()
    {
        $response = $this->getJson("{$this->baseUrl}/settings/export-data");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile',
                    'privacy_settings',
                    'export_generated_at',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data export generated successfully',
            ]);
    }

    /** @test */
    public function test_profile_does_not_expose_password()
    {
        $response = $this->getJson("{$this->baseUrl}/auth/user");

        $response->assertStatus(200)
            ->assertJsonMissing(['password'])
            ->assertJsonMissing(['remember_token']);
    }

    /** @test */
    public function test_can_update_country_code()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'country_code' => '+44',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'country_code' => '+44',
        ]);
    }

    /** @test */
    public function test_can_clear_about_field()
    {
        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'about' => '',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'about' => '',
        ]);
    }

    /** @test */
    public function test_partial_profile_update_does_not_affect_other_fields()
    {
        $originalEmail = $this->user->email;
        $originalPhone = $this->user->phone_number;

        $response = $this->putJson("{$this->baseUrl}/auth/profile", [
            'name' => 'Only Name Changed',
        ]);

        $response->assertStatus(200);

        $this->user->refresh();
        $this->assertEquals('Only Name Changed', $this->user->name);
        $this->assertEquals($originalEmail, $this->user->email);
        $this->assertEquals($originalPhone, $this->user->phone_number);
    }
}
