<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Upload file to Cloudinary
     */
    public function upload($file, $folder = 'media', $options = [])
    {
        try {
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'auto',
                'use_filename' => true,
                'unique_filename' => true,
            ];

            $uploadOptions = array_merge($defaultOptions, $options);

            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                $uploadOptions
            );

            return [
                'success' => true,
                'public_id' => $result['public_id'] ?? null,
                'url' => $result['secure_url'] ?? $result['url'] ?? null,
                'format' => $result['format'] ?? null,
                'resource_type' => $result['resource_type'] ?? 'auto',
                'bytes' => $result['bytes'] ?? 0,
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'created_at' => $result['created_at'] ?? now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload image with transformations
     */
    public function uploadImage($file, $folder = 'images', $generateThumbnail = true)
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'image',
        ];

        $result = $this->upload($file, $folder, $options);

        if ($result['success'] && $generateThumbnail) {
            $result['thumbnail_url'] = $this->generateThumbnail($result['public_id']);
        }

        return $result;
    }

    /**
     * Upload avatar with multiple sizes
     */
    public function uploadAvatar($file, $userId)
    {
        $folder = 'avatars';
        $options = [
            'folder' => $folder,
            'resource_type' => 'image',
            'public_id' => "avatar_{$userId}_" . time(),
        ];

        $result = $this->upload($file, $folder, $options);

        if ($result['success']) {
            $publicId = $result['public_id'];
            
            // Generate different sizes
            $result['avatar_url'] = $result['url'];
            $result['thumbnail_url'] = $this->getTransformedUrl($publicId, 100, 100);
            $result['small_url'] = $this->getTransformedUrl($publicId, 50, 50);
        }

        return $result;
    }

    /**
     * Upload video
     */
    public function uploadVideo($file, $folder = 'videos')
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'video',
        ];

        return $this->upload($file, $folder, $options);
    }

    /**
     * Upload audio
     */
    public function uploadAudio($file, $folder = 'audios')
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'video', // Cloudinary uses 'video' for audio files
        ];

        return $this->upload($file, $folder, $options);
    }

    /**
     * Upload document
     */
    public function uploadDocument($file, $folder = 'documents')
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'raw',
        ];

        return $this->upload($file, $folder, $options);
    }

    /**
     * Generate thumbnail URL
     */
    public function generateThumbnail($publicId, $width = 200, $height = 200)
    {
        return $this->getTransformedUrl($publicId, $width, $height);
    }

    /**
     * Get transformed image URL
     */
    public function getTransformedUrl($publicId, $width, $height, $crop = 'fill')
    {
        return $this->cloudinary->image($publicId)
            ->resize(Resize::fill()->width($width)->height($height))
            ->toUrl();
    }

    /**
     * Delete file from Cloudinary
     */
    public function delete($publicId, $resourceType = 'image')
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => $resourceType
            ]);

            return [
                'success' => $result['result'] === 'ok',
                'result' => $result['result']
            ];

        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed', [
                'error' => $e->getMessage(),
                'public_id' => $publicId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get file info
     */
    public function getFileInfo($publicId, $resourceType = 'image')
    {
        try {
            $result = $this->cloudinary->adminApi()->asset($publicId, [
                'resource_type' => $resourceType
            ]);

            return [
                'success' => true,
                'data' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
