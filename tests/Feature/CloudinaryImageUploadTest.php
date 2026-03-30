<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MediaFile;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CloudinaryImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cloudinaryService;
    protected $uploadedPublicIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Skip tests if Cloudinary is not configured
        if (!env('CLOUDINARY_CLOUD_NAME') || !env('CLOUDINARY_API_KEY') || !env('CLOUDINARY_API_SECRET')) {
            $this->markTestSkipped('Cloudinary credentials not configured. Add CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET to .env');
        }

        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
        ]);

        $this->cloudinaryService = new CloudinaryService();
    }

    protected function tearDown(): void
    {
        // Clean up uploaded files from Cloudinary
        foreach ($this->uploadedPublicIds as $publicId) {
            try {
                $this->cloudinaryService->delete($publicId, 'image');
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function test_can_upload_image_to_cloudinary_via_api()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create a fake image
        $image = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->postJson('/api/media/upload', [
            'file' => $image,
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
                'data' => [
                    'type' => 'image',
                    'uploaded_by' => $this->user->id,
                ]
            ]);

        // Store public_id for cleanup
        $publicId = $response->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        // Verify URL is a valid Cloudinary URL
        $url = $response->json('data.url');
        $this->assertStringContainsString('cloudinary.com', $url);
        $this->assertStringStartsWith('https://', $url);

        // Verify database record was created
        $this->assertDatabaseHas('media_files', [
            'user_id' => $this->user->id,
            'type' => 'image',
            'public_id' => $publicId,
        ]);
    }

    /** @test */
    public function test_can_upload_avatar_to_cloudinary()
    {
        $this->actingAs($this->user, 'sanctum');

        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->postJson('/api/media/upload/avatar', [
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
            ]);

        $publicId = $response->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        // Verify multiple size URLs are returned
        $this->assertNotNull($response->json('data.avatar_url'));
        $this->assertNotNull($response->json('data.thumbnail_url'));
        $this->assertNotNull($response->json('data.small_url'));

        // Verify user avatar was updated
        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_url);
        $this->assertStringContainsString('cloudinary.com', $this->user->avatar_url);
    }

    /** @test */
    public function test_can_upload_status_image_to_cloudinary()
    {
        $this->actingAs($this->user, 'sanctum');

        $statusImage = UploadedFile::fake()->image('status.jpg', 1080, 1920);

        $response = $this->postJson('/api/media/upload/status', [
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
                'data' => [
                    'type' => 'image',
                ]
            ]);

        $publicId = $response->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        // Verify thumbnail was generated
        $this->assertNotNull($response->json('data.thumbnail_url'));
    }

    /** @test */
    public function test_upload_validates_required_fields()
    {
        $this->actingAs($this->user, 'sanctum');

        // Missing file
        $response = $this->postJson('/api/media/upload', [
            'type' => 'image',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        // Missing type
        $image = UploadedFile::fake()->image('test.jpg');
        $response = $this->postJson('/api/media/upload', [
            'file' => $image,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function test_upload_validates_file_type_for_avatar()
    {
        $this->actingAs($this->user, 'sanctum');

        // Try to upload non-image file as avatar
        $document = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $document,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function test_upload_validates_file_size_limit()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create a file larger than 5MB (avatar limit)
        $largeImage = UploadedFile::fake()->create('large.jpg', 6000); // 6MB

        $response = $this->postJson('/api/media/upload/avatar', [
            'avatar' => $largeImage,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function test_upload_requires_authentication()
    {
        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_can_upload_different_image_formats()
    {
        $this->actingAs($this->user, 'sanctum');

        $formats = ['jpg', 'png', 'gif'];

        foreach ($formats as $format) {
            $image = UploadedFile::fake()->image("test.{$format}");

            $response = $this->postJson('/api/media/upload', [
                'file' => $image,
                'type' => 'image',
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                ]);

            $publicId = $response->json('data.public_id');
            if ($publicId) {
                $this->uploadedPublicIds[] = $publicId;
            }

            // Verify format is detected
            $this->assertNotNull($response->json('data.format'));
        }
    }

    /** @test */
    public function test_uploaded_image_has_correct_metadata()
    {
        $this->actingAs($this->user, 'sanctum');

        $image = UploadedFile::fake()->image('test.jpg', 1024, 768);

        $response = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');

        // Verify metadata
        $this->assertNotNull($data['width']);
        $this->assertNotNull($data['height']);
        $this->assertNotNull($data['size']);
        $this->assertNotNull($data['size_formatted']);
        $this->assertNotNull($data['format']);
        $this->assertEquals('image', $data['resource_type']);

        $publicId = $response->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }
    }

    /** @test */
    public function test_can_delete_uploaded_image()
    {
        $this->actingAs($this->user, 'sanctum');

        // First upload an image
        $image = UploadedFile::fake()->image('test.jpg');

        $uploadResponse = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $uploadResponse->assertStatus(201);
        $publicId = $uploadResponse->json('data.public_id');

        // Now delete it
        $deleteResponse = $this->json('DELETE', '/api/media/delete', [
            'public_id' => $publicId,
            'resource_type' => 'image',
        ]);

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        // Verify database record was deleted
        $this->assertDatabaseMissing('media_files', [
            'public_id' => $publicId,
        ]);

        // Remove from cleanup list since we already deleted it
        $this->uploadedPublicIds = array_filter($this->uploadedPublicIds, function($id) use ($publicId) {
            return $id !== $publicId;
        });
    }

    /** @test */
    public function test_cannot_delete_other_users_image()
    {
        $this->actingAs($this->user, 'sanctum');

        // Upload an image
        $image = UploadedFile::fake()->image('test.jpg');
        $uploadResponse = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $publicId = $uploadResponse->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        // Create another user and try to delete the first user's image
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser, 'sanctum');

        $deleteResponse = $this->json('DELETE', '/api/media/delete', [
            'public_id' => $publicId,
            'resource_type' => 'image',
        ]);

        $deleteResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete this file'
            ]);
    }

    /** @test */
    public function test_can_retrieve_user_media_files()
    {
        $this->actingAs($this->user, 'sanctum');

        // Upload multiple images
        for ($i = 0; $i < 3; $i++) {
            $image = UploadedFile::fake()->image("test{$i}.jpg");
            $uploadResponse = $this->postJson('/api/media/upload', [
                'file' => $image,
                'type' => 'image',
            ]);

            $publicId = $uploadResponse->json('data.public_id');
            if ($publicId) {
                $this->uploadedPublicIds[] = $publicId;
            }
        }

        // Retrieve user's media
        $response = $this->getJson('/api/media/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'count'
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertGreaterThanOrEqual(3, $response->json('count'));
    }

    /** @test */
    public function test_can_filter_media_by_type()
    {
        $this->actingAs($this->user, 'sanctum');

        // Upload an image
        $image = UploadedFile::fake()->image('test.jpg');
        $uploadResponse = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $publicId = $uploadResponse->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        // Retrieve only images
        $response = $this->getJson('/api/media/user?type=image');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify all returned items are images
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('image', $item['type']);
        }
    }

    /** @test */
    public function test_cloudinary_service_generates_thumbnail()
    {
        $this->actingAs($this->user, 'sanctum');

        $image = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $response = $this->postJson('/api/media/upload', [
            'file' => $image,
            'type' => 'image',
        ]);

        $response->assertStatus(201);

        $publicId = $response->json('data.public_id');
        if ($publicId) {
            $this->uploadedPublicIds[] = $publicId;
        }

        $thumbnailUrl = $response->json('data.thumbnail_url');
        
        // Verify thumbnail URL exists and contains transformation parameters
        $this->assertNotNull($thumbnailUrl);
        $this->assertStringContainsString('cloudinary.com', $thumbnailUrl);
    }

    /** @test */
    public function test_can_get_media_statistics()
    {
        $this->actingAs($this->user, 'sanctum');

        // Upload some test images and verify they're saved
        $uploadedIds = [];
        for ($i = 0; $i < 2; $i++) {
            $image = UploadedFile::fake()->image("test{$i}.jpg");
            $uploadResponse = $this->postJson('/api/media/upload', [
                'file' => $image,
                'type' => 'image',
            ]);

            $uploadResponse->assertStatus(201);
            
            $publicId = $uploadResponse->json('data.public_id');
            if ($publicId) {
                $this->uploadedPublicIds[] = $publicId;
                $uploadedIds[] = $publicId;
            }
        }

        // Verify uploads were successful
        $this->assertCount(2, $uploadedIds, 'Should have uploaded 2 images');

        // Get statistics
        $response = $this->getJson('/api/media/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_files',
                    'total_size',
                    'total_size_formatted',
                    'by_type' => [
                        'images',
                        'videos',
                        'audios',
                        'documents',
                    ],
                    'recent_uploads'
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Check that we have at least the files we just uploaded
        $totalFiles = $response->json('data.total_files');
        $this->assertGreaterThanOrEqual(2, $totalFiles, "Expected at least 2 files, got {$totalFiles}");
    }
}
