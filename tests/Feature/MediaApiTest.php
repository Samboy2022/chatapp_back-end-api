<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class MediaApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_upload_avatar()
    {
        $image = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $response = $this->post('/api/media/upload/avatar', ['avatar' => $image]);
        $response->assertStatus(201); // Avatar upload returns 201 for successful creation
    }

    public function test_can_upload_chat_avatar()
    {
        $chat = \App\Models\Chat::factory()->create(['type' => 'group']);
        $chat->participants()->attach($this->user->id, ['role' => 'admin']);

        $image = UploadedFile::fake()->image('chat-avatar.png');
        $response = $this->post('/api/media/upload/chat-avatar', [
            'avatar' => $image,
            'chat_id' => $chat->id,
        ]);
        $response->assertStatus(201); // Chat avatar upload returns 201
    }

    public function test_can_upload_status_media()
    {
        $image = UploadedFile::fake()->image('status.jpg');
        $response = $this->post('/api/media/upload/status', [
            'file' => $image,
            'type' => 'image',
        ]);
        $response->assertStatus(201); // Status media upload returns 201
    }

    public function test_can_upload_general_media()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);
        $response = $this->post('/api/media/upload', [
            'file' => $file,
            'type' => 'document',
        ]);
        $response->assertStatus(201); // General media upload returns 201
    }

    public function test_can_upload_video_file()
    {
        $video = UploadedFile::fake()->create('video.mp4', 5120, 'video/mp4');
        $response = $this->post('/api/media/upload', [
            'file' => $video,
            'type' => 'video',
        ]);
        $response->assertStatus(201); // Video upload returns 201
    }

    public function test_can_upload_audio_file()
    {
        $audio = UploadedFile::fake()->create('audio.mp3', 2048, 'audio/mpeg');
        $response = $this->post('/api/media/upload', [
            'file' => $audio,
            'type' => 'audio',
        ]);
        $response->assertStatus(201); // Audio upload returns 201
    }

    public function test_media_upload_validation_errors()
    {
        $response = $this->postJson('/api/media/upload', []);
        $response->assertStatus(422);
    }

    public function test_can_delete_uploaded_media()
    {
        $file = UploadedFile::fake()->create('to-delete.pdf', 1024);
        $uploadResponse = $this->post('/api/media/upload', [
            'file' => $file,
            'type' => 'document',
        ]);

        $filePath = $uploadResponse->json('data.filename');
        $response = $this->deleteJson('/api/media/delete', [
            'file_path' => 'media/documents/' . $filePath,
        ]);
        $response->assertStatus(200);
    }

    public function test_avatar_upload_requires_authentication()
    {
        $image = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->post('/api/media/upload/avatar', ['avatar' => $image]);
        $response->assertStatus(401);
    }

    public function test_media_upload_invalid_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('malware.exe', 1024);
        $response = $this->post('/api/media/upload', [
            'file' => $invalidFile,
            'type' => 'document',
        ]);
        $response->assertStatus(422);
    }

    public function test_can_upload_multiple_files()
    {
        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->create('document.pdf', 1024),
        ];

        foreach ($files as $file) {
            $response = $this->post('/api/media/upload', [
                'file' => $file,
                'type' => 'document',
            ]);
            $response->assertStatus(201); // Media upload returns 201
        }
    }
}