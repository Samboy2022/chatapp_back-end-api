<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\MediaFile;
use Laravel\Sanctum\Sanctum;

class MediaUploadApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $baseUrl = '/api/media';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
        ]);
        
        Sanctum::actingAs($this->user);
        
        // Mock Cloudinary service
        $this->mockCloudinaryService();
    }

    /**
     * Mock Cloudinary service to avoid actual uploads during testing
     */
    protected function mockCloudinaryService()
    {
        $this->mock(\App\Services\CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('uploadImage')->andReturn([
                'success' => true,
                'public_id' => 'test/image_' . time(),
                'url' => 'https://res.cloudinary.com/test/image/upload/test.jpg',
                'thumbnail_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_200,w_200/test.jpg',
                'format' => 'jpg',
                'resource_type' => 'image',
                'bytes' => 102400,
                'width' => 1920,
                'height' => 1080,
            ]);

            $mock->shouldReceive('uploadVideo')->andReturn([
                'success' => true,
                'public_id' => 'test/video_' . time(),
                'url' => 'https://res.cloudinary.com/test/video/upload/test.mp4',
                'format' => 'mp4',
                'resource_type' => 'video',
                'bytes' => 5242880,
                'width' => 1920,
                'height' => 1080,
            ]);

            $mock->shouldReceive('uploadAudio')->andReturn([
                'success' => true,
                'public_id' => 'test/audio_' . time(),
                'url' => 'https://res.cloudinary.com/test/video/upload/test.mp3',
                'format' => 'mp3',
                'resource_type' => 'video',
                'bytes' => 2097152,
            ]);

            $mock->shouldReceive('uploadDocument')->andReturn([
                'success' => true,
                'public_id' => 'test/document_' . time(),
                'url' => 'https://res.cloudinary.com/test/raw/upload/test.pdf',
                'format' => 'pdf',
                'resource_type' => 'raw',
                'bytes' => 1048576,
            ]);

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
    public function test_can_upload_general_media_file()
    {
        $file = UploadedFile::fake()->image('test-image.jpg', 1920, 1080);

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'public_id',
                    'url',
                    'thumbnail_url',
                    'type',
                    'format',
                    'resource_type',
                    'size',
                    'size_formatted',
                    'width',
                    'height',
                    'uploaded_by',
                    'uploaded_at',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'image',
        ]);
    }

    /** @test */
    public function test_can_upload_avatar()
    {
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->postJson("{$this->baseUrl}/upload/avatar", [
            'avatar' => $avatar,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'public_id',
                    'avatar_url',
                    'thumbnail_url',
                    'small_url',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Avatar uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'usage_type' => 'avatar',
        ]);

        // Check user avatar was updated
        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_url);
    }

    /** @test */
    public function test_can_upload_chat_avatar()
    {
        $chat = Chat::factory()->create(['type' => 'group']);
        $chat->participants()->attach($this->user->id, ['role' => 'admin']);

        $chatAvatar = UploadedFile::fake()->image('chat-avatar.png', 400, 400);

        $response = $this->postJson("{$this->baseUrl}/upload/chat-avatar", [
            'chat_avatar' => $chatAvatar,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'public_id',
                    'avatar_url',
                    'thumbnail_url',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Chat avatar uploaded successfully'
            ]);
    }

    /** @test */
    public function test_can_upload_status_media()
    {
        $statusImage = UploadedFile::fake()->image('status.jpg', 1080, 1920);

        $response = $this->postJson("{$this->baseUrl}/upload/status", [
            'media' => $statusImage,
            'type' => 'image',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'public_id',
                    'url',
                    'thumbnail_url',
                    'type',
                    'format',
                    'size',
                    'size_formatted',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Status media uploaded successfully'
            ]);
    }

    /** @test */
    public function test_can_upload_video_file()
    {
        $video = UploadedFile::fake()->create('video.mp4', 5120, 'video/mp4');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $video,
            'type' => 'video',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'video',
        ]);
    }

    /** @test */
    public function test_can_upload_audio_file()
    {
        $audio = UploadedFile::fake()->create('audio.mp3', 2048, 'audio/mpeg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $audio,
            'type' => 'audio',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'audio',
        ]);
    }

    /** @test */
    public function test_can_upload_document_file()
    {
        $document = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $document,
            'type' => 'document',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'document',
        ]);
    }

    /** @test */
    public function test_can_delete_media_with_public_id()
    {
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $this->user->id,
            'public_id' => 'test/image_123',
            'resource_type' => 'image',
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/delete", [
            'public_id' => 'test/image_123',
            'resource_type' => 'image',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        $this->assertSoftDeleted('media_files', [
            'id' => $mediaFile->id,
        ]);
    }

    /** @test */
    public function test_can_delete_media_with_file_path()
    {
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $this->user->id,
            'public_id' => 'avatars/avatar',
            'resource_type' => 'image',
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/delete", [
            'file_path' => '/storage/avatars/avatar.jpg',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
    }

    /** @test */
    public function test_upload_requires_authentication()
    {
        Sanctum::actingAs(null);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_upload_validates_required_file()
    {
        $response = $this->postJson("{$this->baseUrl}/upload", [
            'type' => 'image',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function test_upload_validates_required_type()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function test_upload_validates_type_values()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $file,
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function test_avatar_upload_validates_required_avatar()
    {
        $response = $this->postJson("{$this->baseUrl}/upload/avatar", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function test_avatar_upload_validates_image_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->postJson("{$this->baseUrl}/upload/avatar", [
            'avatar' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function test_chat_avatar_upload_validates_required_fields()
    {
        $response = $this->postJson("{$this->baseUrl}/upload/chat-avatar", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chat_avatar', 'chat_id']);
    }

    /** @test */
    public function test_chat_avatar_upload_validates_chat_exists()
    {
        $chatAvatar = UploadedFile::fake()->image('chat-avatar.png');

        $response = $this->postJson("{$this->baseUrl}/upload/chat-avatar", [
            'chat_avatar' => $chatAvatar,
            'chat_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chat_id']);
    }

    /** @test */
    public function test_status_media_upload_validates_required_fields()
    {
        $response = $this->postJson("{$this->baseUrl}/upload/status", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['media', 'type']);
    }

    /** @test */
    public function test_status_media_upload_validates_type_values()
    {
        $file = UploadedFile::fake()->image('status.jpg');

        $response = $this->postJson("{$this->baseUrl}/upload/status", [
            'media' => $file,
            'type' => 'audio',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function test_delete_validates_required_identifier()
    {
        $response = $this->deleteJson("{$this->baseUrl}/delete", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['public_id', 'file_path']);
    }

    /** @test */
    public function test_cannot_delete_other_users_media()
    {
        $otherUser = User::factory()->create();
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $otherUser->id,
            'public_id' => 'test/other_user_image',
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/delete", [
            'public_id' => 'test/other_user_image',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete this file'
            ]);
    }

    /** @test */
    public function test_can_upload_media_with_chat_id()
    {
        $chat = Chat::factory()->create();
        $chat->participants()->attach($this->user->id);

        $file = UploadedFile::fake()->image('chat-image.jpg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $file,
            'type' => 'image',
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
            'type' => 'image',
        ]);
    }

    /** @test */
    public function test_upload_status_video()
    {
        $video = UploadedFile::fake()->create('status-video.mp4', 10240, 'video/mp4');

        $response = $this->postJson("{$this->baseUrl}/upload/status", [
            'media' => $video,
            'type' => 'video',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Status media uploaded successfully'
            ]);
    }

    /** @test */
    public function test_upload_voice_message()
    {
        $voice = UploadedFile::fake()->create('voice.ogg', 512, 'audio/ogg');

        $response = $this->postJson("{$this->baseUrl}/upload", [
            'file' => $voice,
            'type' => 'voice',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'voice',
        ]);
    }
}
