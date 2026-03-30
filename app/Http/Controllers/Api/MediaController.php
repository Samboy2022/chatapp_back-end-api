<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
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

            // Save to database
            $mediaFile = MediaFile::create([
                'user_id' => $userId,
                'public_id' => $result['public_id'],
                'url' => $result['url'],
                'thumbnail_url' => $result['thumbnail_url'] ?? null,
                'type' => $type,
                'format' => $result['format'] ?? 'unknown',
                'resource_type' => $result['resource_type'] ?? 'auto',
                'size' => $result['bytes'] ?? 0,
                'size_formatted' => $this->formatBytes($result['bytes'] ?? 0),
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'folder' => 'media/' . $type . 's',
                'chat_id' => $request->chat_id,
                'usage_type' => 'message',
            ]);

            // Prepare response
            $response = [
                'success' => true,
                'data' => [
                    'id' => $mediaFile->id,
                    'public_id' => $mediaFile->public_id,
                    'url' => $mediaFile->url,
                    'thumbnail_url' => $mediaFile->thumbnail_url,
                    'type' => $mediaFile->type,
                    'format' => $mediaFile->format,
                    'resource_type' => $mediaFile->resource_type,
                    'size' => $mediaFile->size,
                    'size_formatted' => $mediaFile->size_formatted,
                    'width' => $mediaFile->width,
                    'height' => $mediaFile->height,
                    'uploaded_by' => $userId,
                    'uploaded_at' => $mediaFile->created_at->toISOString(),
                    'chat_id' => $mediaFile->chat_id
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

            // Save to database
            $mediaFile = MediaFile::create([
                'user_id' => $userId,
                'public_id' => $result['public_id'],
                'url' => $result['avatar_url'],
                'thumbnail_url' => $result['thumbnail_url'],
                'type' => 'image',
                'format' => $result['format'] ?? 'unknown',
                'resource_type' => 'image',
                'size' => $result['bytes'] ?? 0,
                'size_formatted' => $this->formatBytes($result['bytes'] ?? 0),
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'folder' => 'avatars',
                'usage_type' => 'avatar',
                'metadata' => [
                    'small_url' => $result['small_url']
                ]
            ]);

            // Update user avatar in database
            $user = Auth::user();
            $user->avatar_url = $result['avatar_url'];
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $mediaFile->id,
                    'public_id' => $mediaFile->public_id,
                    'avatar_url' => $mediaFile->url,
                    'thumbnail_url' => $mediaFile->thumbnail_url,
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
                'chat_avatar' => 'required|image|max:5120',
                'chat_id' => 'required|exists:chats,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('chat_avatar');
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
                'media' => 'required|file|max:50000', // 50MB max
                'type' => 'required|string|in:image,video'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('media');
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
                'public_id' => 'required_without:file_path|string',
                'file_path' => 'required_without:public_id|string',
                'resource_type' => 'nullable|string|in:image,video,raw'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Support both public_id and file_path for backward compatibility
            $publicId = $request->public_id;
            
            // If file_path is provided, extract public_id from it
            if (!$publicId && $request->file_path) {
                // Extract public_id from file path (e.g., /storage/avatars/avatar.jpg -> avatars/avatar)
                $filePath = $request->file_path;
                $publicId = preg_replace('/^\/?(storage\/)?/', '', $filePath);
                $publicId = preg_replace('/\.[^.]+$/', '', $publicId); // Remove extension
            }

            $resourceType = $request->resource_type ?? 'image';

            // Try to find and delete from database first
            $mediaFile = MediaFile::where('public_id', $publicId)->first();
            if ($mediaFile) {
                // Check if user owns the file
                if ($mediaFile->user_id !== Auth::id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to delete this file'
                    ], 403);
                }
                
                $resourceType = $mediaFile->resource_type;
                $mediaFile->delete();
            }

            // Delete from Cloudinary
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

    /**
     * Get user's media files
     */
    public function getUserMedia(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $type = $request->query('type'); // Optional filter by type
            $limit = $request->query('limit', 50);

            $query = MediaFile::byUser($userId)
                ->orderBy('created_at', 'desc');

            if ($type) {
                $query->ofType($type);
            }

            $media = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $media,
                'count' => $media->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat media files
     */
    public function getChatMedia(Request $request, $chatId): JsonResponse
    {
        try {
            $type = $request->query('type'); // Optional filter by type
            $limit = $request->query('limit', 50);

            $query = MediaFile::byChat($chatId)
                ->orderBy('created_at', 'desc');

            if ($type) {
                $query->ofType($type);
            }

            $media = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $media,
                'count' => $media->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chat media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media file by ID
     */
    public function getMediaById($id): JsonResponse
    {
        try {
            $media = MediaFile::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $media
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Media file not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get media statistics
     */
    public function getMediaStats(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $stats = [
                'total_files' => MediaFile::byUser($userId)->count(),
                'total_size' => MediaFile::byUser($userId)->sum('size'),
                'by_type' => [
                    'images' => MediaFile::byUser($userId)->ofType('image')->count(),
                    'videos' => MediaFile::byUser($userId)->ofType('video')->count(),
                    'audios' => MediaFile::byUser($userId)->ofType('audio')->count(),
                    'documents' => MediaFile::byUser($userId)->ofType('document')->count(),
                ],
                'recent_uploads' => MediaFile::byUser($userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            ];

            // Format total size
            $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve media statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
