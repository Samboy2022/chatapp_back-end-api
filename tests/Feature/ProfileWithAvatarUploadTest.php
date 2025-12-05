<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\MediaFile;
use Laravel\Sanctum\Sanctum;

class ProfileWithAvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'password' => Hash::make('password123'),
        ]);
        
        Sanctum::actingAs($this->user);
        
        // Mock Cloudinary service
        $this->mockCloudinaryService();
    }

    protected function mockCloudinaryService()
    {
        $this->mock(\App\Services\CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('uploadAvatar')->andReturn([
                'success' => true,
                'public_id' => 'avatars/avatar_' . time(),
                'avatar_url' => 'https://res.cloudinary.com/test/image/upload/avatar.jpg',
                'thumbnail_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_100,w_100/avatar.jpg',
                'small_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_50,w_50/avatar.jpg',
                'format' => 'jpg',
                'bytes' => 51200,
                'width' => 500,
                'height' => 500,
            ]);

            $mock->shouldReceive('delete')->andReturn([
                'success' => true,
                'result' => 'ok'
            ]);
        });
    }

    /** @test */
    public function test_complete_profile_update_workflow()
    {
        // Step 1: Upload avatar
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);
        
        $uploadResponse = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar,
        ]);

        $uploadResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
            ]);

        $avatarUrl = $uploadResponse->json('data.avatar_url');

        // Step 2: Update profile with new avatar URL and other info
        $profileResponse = $this->putJson('/api/auth/profile', [
            'name' => 'Updated User Name',
            'about' => 'My new status message',
            'avatar_url' => $avatarUrl,
        ]);

        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        // Step 3: Verify profile was updated
        $this->user->refresh();
        $this->assertEquals('Updated User Name', $this->user->name);
        $this->assertEquals('My new status message', $this->user->about);
        $this->assertEquals($avatarUrl, $this->user->avatar_url);

        // Step 4: Verify avatar is in database
        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'usage_type' => 'avatar',
        ]);
    }

    /** @test */
    public function test_can_upload_avatar_and_retrieve_updated_profile()
    {
        // Upload avatar
        $avatar = UploadedFile::fake()->image('new-avatar.jpg');
        
        $uploadResponse = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar,
        ]);

        $uploadResponse->assertStatus(201);
        $avatarUrl = $uploadResponse->json('data.avatar_url');

        // Get updated profile
        $profileResponse = $this->getJson('/api/auth/user');

        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'avatar_url' => $avatarUrl,
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_can_change_avatar_multiple_times()
    {
        // Upload first avatar
        $avatar1 = UploadedFile::fake()->image('avatar1.jpg');
        $response1 = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar1,
        ]);
        $response1->assertStatus(201);
        $firstAvatarUrl = $response1->json('data.avatar_url');

        // Upload second avatar
        $avatar2 = UploadedFile::fake()->image('avatar2.jpg');
        $response2 = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar2,
        ]);
        $response2->assertStatus(201);
        $secondAvatarUrl = $response2->json('data.avatar_url');

        // Verify latest avatar is set
        $this->user->refresh();
        $this->assertEquals($secondAvatarUrl, $this->user->avatar_url);

        // Verify both avatars are in database
        $this->assertEquals(2, MediaFile::where('user_id', $this->user->id)
            ->where('usage_type', 'avatar')
            ->count());
    }

    /** @test */
    public function test_profile_update_with_email_and_phone_change()
    {
        $response = $this->putJson('/api/auth/profile', [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'phone_number' => '+9876543210',
            'country_code' => '+98',
            'about' => 'New about text',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'phone_number' => '+9876543210',
            'country_code' => '+98',
            'about' => 'New about text',
        ]);
    }

    /** @test */
    public function test_can_update_profile_and_change_password_simultaneously()
    {
        $response = $this->putJson('/api/auth/profile', [
            'name' => 'Updated Name',
            'about' => 'Updated about',
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);

        // Verify profile updated
        $this->user->refresh();
        $this->assertEquals('Updated Name', $this->user->name);
        $this->assertEquals('Updated about', $this->user->about);

        // Verify password changed
        $this->assertTrue(Hash::check('newpassword456', $this->user->password));
    }

    /** @test */
    public function test_avatar_upload_updates_user_avatar_url_automatically()
    {
        $this->assertNull($this->user->avatar_url);

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar,
        ]);

        $response->assertStatus(201);

        // Verify user's avatar_url was automatically updated
        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_url);
        $this->assertStringContainsString('cloudinary.com', $this->user->avatar_url);
    }

    /** @test */
    public function test_can_get_user_media_after_avatar_upload()
    {
        // Upload avatar
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson('/api/media/upload/avatar', ['avatar' => $avatar]);

        // Get user media
        $response = $this->getJson('/api/media/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'public_id',
                        'url',
                        'type',
                        'usage_type',
                    ]
                ],
                'count'
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertGreaterThan(0, $response->json('count'));
    }

    /** @test */
    public function test_can_get_media_stats_after_uploads()
    {
        // Upload avatar
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $this->postJson('/api/media/upload/avatar', ['avatar' => $avatar]);

        // Get media stats
        $response = $this->getJson('/api/media/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_files',
                    'total_size',
                    'total_size_formatted',
                    'by_type',
                    'recent_uploads',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertGreaterThan(0, $response->json('data.total_files'));
    }

    /** @test */
    public function test_complete_user_lifecycle()
    {
        // 1. Register (already done in setUp)
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 2. Upload avatar
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $uploadResponse = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $avatar,
        ]);
        $uploadResponse->assertStatus(201);

        // 3. Update profile
        $profileResponse = $this->putJson('/api/auth/profile', [
            'name' => 'Complete User',
            'about' => 'Living my best life!',
        ]);
        $profileResponse->assertStatus(200);

        // 4. Change password
        $passwordResponse = $this->putJson('/api/auth/profile', [
            'current_password' => 'password123',
            'new_password' => 'newsecurepass789',
            'new_password_confirmation' => 'newsecurepass789',
        ]);
        $passwordResponse->assertStatus(200);

        // 5. Get profile to verify all changes
        $getResponse = $this->getJson('/api/auth/user');
        $getResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Complete User',
                        'about' => 'Living my best life!',
                    ]
                ]
            ]);

        // 6. Verify password was changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('newsecurepass789', $this->user->password));

        // 7. Export data
        $exportResponse = $this->getJson('/api/settings/export-data');
        $exportResponse->assertStatus(200);

        // 8. Delete account
        $deleteResponse = $this->deleteJson('/api/settings/delete-account', [
            'password' => 'newsecurepass789',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);
        $deleteResponse->assertStatus(200);

        // 9. Verify account is soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_access_profile_endpoints()
    {
        Sanctum::actingAs(null);

        $endpoints = [
            ['method' => 'get', 'url' => '/api/auth/user'],
            ['method' => 'put', 'url' => '/api/auth/profile'],
            ['method' => 'get', 'url' => '/api/settings/profile'],
            ['method' => 'put', 'url' => '/api/settings/profile'],
            ['method' => 'delete', 'url' => '/api/settings/delete-account'],
            ['method' => 'get', 'url' => '/api/settings/export-data'],
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->{$endpoint['method'] . 'Json'}($endpoint['url']);
            $response->assertStatus(401);
        }
    }
}
