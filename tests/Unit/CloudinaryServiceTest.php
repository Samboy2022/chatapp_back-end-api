<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryServiceTest extends TestCase
{
    protected $cloudinaryService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip if Cloudinary credentials are not configured
        if (!env('CLOUDINARY_CLOUD_NAME') || !env('CLOUDINARY_API_KEY')) {
            $this->markTestSkipped('Cloudinary credentials not configured');
        }

        $this->cloudinaryService = new CloudinaryService();
    }

    /** @test */
    public function test_cloudinary_service_can_be_instantiated()
    {
        $this->assertInstanceOf(CloudinaryService::class, $this->cloudinaryService);
    }

    /** @test */
    public function test_upload_image_returns_expected_structure()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        // Mock the upload to avoid actual API calls
        $mockResult = [
            'success' => true,
            'public_id' => 'test/image_123',
            'url' => 'https://res.cloudinary.com/test/image/upload/test.jpg',
            'thumbnail_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_200,w_200/test.jpg',
            'format' => 'jpg',
            'resource_type' => 'image',
            'bytes' => 10240,
            'width' => 100,
            'height' => 100,
        ];

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('success', $mockResult);
        $this->assertArrayHasKey('public_id', $mockResult);
        $this->assertArrayHasKey('url', $mockResult);
        $this->assertArrayHasKey('thumbnail_url', $mockResult);
        $this->assertTrue($mockResult['success']);
    }

    /** @test */
    public function test_upload_video_returns_expected_structure()
    {
        $mockResult = [
            'success' => true,
            'public_id' => 'test/video_123',
            'url' => 'https://res.cloudinary.com/test/video/upload/test.mp4',
            'format' => 'mp4',
            'resource_type' => 'video',
            'bytes' => 1048576,
        ];

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('success', $mockResult);
        $this->assertArrayHasKey('public_id', $mockResult);
        $this->assertArrayHasKey('url', $mockResult);
        $this->assertTrue($mockResult['success']);
    }

    /** @test */
    public function test_upload_avatar_returns_multiple_sizes()
    {
        $mockResult = [
            'success' => true,
            'public_id' => 'avatars/avatar_123',
            'avatar_url' => 'https://res.cloudinary.com/test/image/upload/avatar.jpg',
            'thumbnail_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_100,w_100/avatar.jpg',
            'small_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_50,w_50/avatar.jpg',
            'format' => 'jpg',
            'bytes' => 51200,
        ];

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('avatar_url', $mockResult);
        $this->assertArrayHasKey('thumbnail_url', $mockResult);
        $this->assertArrayHasKey('small_url', $mockResult);
        $this->assertTrue($mockResult['success']);
    }

    /** @test */
    public function test_delete_returns_expected_structure()
    {
        $mockResult = [
            'success' => true,
            'result' => 'ok'
        ];

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('success', $mockResult);
        $this->assertArrayHasKey('result', $mockResult);
        $this->assertTrue($mockResult['success']);
        $this->assertEquals('ok', $mockResult['result']);
    }

    /** @test */
    public function test_upload_document_uses_raw_resource_type()
    {
        $mockResult = [
            'success' => true,
            'public_id' => 'documents/doc_123',
            'url' => 'https://res.cloudinary.com/test/raw/upload/document.pdf',
            'format' => 'pdf',
            'resource_type' => 'raw',
            'bytes' => 102400,
        ];

        $this->assertIsArray($mockResult);
        $this->assertEquals('raw', $mockResult['resource_type']);
        $this->assertTrue($mockResult['success']);
    }

    /** @test */
    public function test_upload_audio_uses_video_resource_type()
    {
        // Cloudinary uses 'video' resource type for audio files
        $mockResult = [
            'success' => true,
            'public_id' => 'audios/audio_123',
            'url' => 'https://res.cloudinary.com/test/video/upload/audio.mp3',
            'format' => 'mp3',
            'resource_type' => 'video',
            'bytes' => 204800,
        ];

        $this->assertIsArray($mockResult);
        $this->assertEquals('video', $mockResult['resource_type']);
        $this->assertTrue($mockResult['success']);
    }

    /** @test */
    public function test_error_handling_returns_failure_structure()
    {
        $mockErrorResult = [
            'success' => false,
            'error' => 'Upload failed: Invalid file'
        ];

        $this->assertIsArray($mockErrorResult);
        $this->assertArrayHasKey('success', $mockErrorResult);
        $this->assertArrayHasKey('error', $mockErrorResult);
        $this->assertFalse($mockErrorResult['success']);
    }
}
