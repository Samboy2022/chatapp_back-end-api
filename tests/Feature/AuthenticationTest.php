<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone_number'],
                    'token',
                    'token_type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
        ]);
    }

    /** @test */
    public function user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_cannot_register_with_duplicate_phone()
    {
        User::factory()->create(['phone_number' => '+1234567890']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function user_can_login_with_email()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_online' => true,
        ]);
    }

    /** @test */
    public function user_can_login_with_phone()
    {
        $user = User::factory()->create([
            'phone_number' => '+1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => '+1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_profile()
    {
        $user = $this->authenticateUser();

        $response = $this->putJson('/api/auth/profile', [
            'name' => 'Updated Name',
            'about' => 'Updated about text',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'about' => 'Updated about text',
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_privacy_settings()
    {
        $user = $this->authenticateUser();

        $response = $this->putJson('/api/auth/privacy', [
            'privacy_last_seen' => 'contacts',
            'privacy_profile_photo' => 'nobody',
            'read_receipts' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Privacy settings updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'privacy_last_seen' => 'contacts',
            'privacy_profile_photo' => 'nobody',
            'read_receipts' => false,
        ]);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = $this->authenticateUser();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_online' => false,
        ]);
    }

    /** @test */
    public function authenticated_user_can_refresh_token()
    {
        $user = $this->authenticateUser();

        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function registration_validates_required_fields()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name', 'email', 'phone_number', 'password'
            ]);
    }

    /** @test */
    public function registration_validates_password_confirmation()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_validates_required_fields()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);
    }
}