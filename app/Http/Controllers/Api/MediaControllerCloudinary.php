<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MediaControllerCloudinary extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Upload media file (images, videos, audio, documents)
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:100000', // 100MB max
                'type' => 'required|string|in:image,video,audio,document,voice',
                'chat_id' => 'nullable|exists:chats,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $type = $request->type;
            $userId = Auth::id();

            // Upload to Cloudinary based on type
            $result = null;
            switch ($type) {
                case 'image':
                    $result = $this->cloudinary->uploadImage($file, 'media/images');
                    break;
                case 'video':
                    $result = $this->cloudinary->uploadVideo($file, 'media/videos');
                    break;
                case 'audio':
                case 'voice':
                    $result = $this->cloudinary->uploadAudio($file, 'media/audios');
                    break;
                case 'document':
                    $result = $this->cloudinary->uploadDocument($file, 'media/documents');
                    break;
            }

            if (!$result || !$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed',
                    'error' => $result['error'] ?? 'Unknown error'
                ], 500);
            }

            // Prepare response
            $response = [
                'success' => true,
                'data' => [
                    'public_id' => $result['public_id'],
                    'url' => $result['url'],
                    'thumbnail_url' => $result['thumbnail_url'] ?? null,
                    'type' => $type,
                    'format' => $result['format'],
                    'resource_type' => $result['resource_type'],
                    'size' => $result['bytes'],
                    'size_formatted' => $this->formatBytes($result['bytes']),
                    'width' => $result['width'] ?? null,
                    'height' => $result['height'] ?? null,
                    'uploaded_by' => $userId,
                    'uploaded_at' => now()->toISOString(),
                    'chat_id' => $request->chat_id
                ],
                'message' => 'File uploaded successfully'
            ];

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('avatar');
            $userId = Auth::id();

            // Upload avatar with multiple sizes
            $result = $this->cloudinary->uploadAvatar($file, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Avatar upload failed',
                    'error' => $result['error']
                ], 500);
            }

            // Update user avatar in database
            $user = Auth::user();
            $user->avatar_url = $result['avatar_url'];
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'public_id' => $result['public_id'],
                    'avatar_url' => $result['avatar_url'],
                    'thumbnail_url' => $result['thumbnail_url'],
                    'small_url' => $result['small_url'],
                ],
                'message' => 'Avatar uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload chat avatar
     */
    public function uploadChatAvatar(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|max:5120',
                'chat_id' => 'required|exists:chats,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('avatar');
            $chatId = $request->chat_id;

            // Upload to Cloudinary
            $result = $this->cloudinary->uploadImage($file, 'chat-avatars', true);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat avatar upload failed',
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'public_id' => $result['public_id'],
                    'avatar_url' => $result['url'],
                    'thumbnail_url' => $result['thumbnail_url'],
                ],
                'message' => 'Chat avatar uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chat avatar upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload status media
     */
    public function uploadStatusMedia(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:50000', // 50MB max
                'type' => 'required|string|in:image,video'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $type = $request->type;
            $userId = Auth::id();

            // Upload to Cloudinary
            $result = $type === 'image' 
                ? $this->cloudinary->uploadImage($file, 'status', true)
                : $this->cloudinary->uploadVideo($file, 'status');

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status media upload failed',
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'public_id' => $result['public_id'],
                    'url' => $result['url'],
                    'thumbnail_url' => $result['thumbnail_url'] ?? null,
                    'type' => $type,
                    'format' => $result['format'],
                    'size' => $result['bytes'],
                    'size_formatted' => $this->formatBytes($result['bytes']),
                ],
                'message' => 'Status media uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status media upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete media
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'public_id' => 'required|string',
                'resource_type' => 'nullable|string|in:image,video,raw'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $publicId = $request->public_id;
            $resourceType = $request->resource_type ?? 'image';

            $result = $this->cloudinary->delete($publicId, $resourceType);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delete failed',
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
