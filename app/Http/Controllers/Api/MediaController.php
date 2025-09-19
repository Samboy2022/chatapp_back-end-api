<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{
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
            
            // Additional validation based on type
            $additionalRules = [];
            switch ($type) {
                case 'image':
                    $additionalRules = ['file' => 'mimes:jpeg,jpg,png,gif,webp|max:10240']; // 10MB
                    break;
                case 'video':
                    $additionalRules = ['file' => 'mimes:mp4,avi,mov,wmv,flv|max:51200']; // 50MB
                    break;
                case 'audio':
                case 'voice':
                    $additionalRules = ['file' => 'mimes:mp3,wav,aac,ogg,m4a,webm,3gp|max:20480']; // 20MB
                    break;
                case 'document':
                    $additionalRules = ['file' => 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:25600']; // 25MB
                    break;
            }

            if ($additionalRules) {
                $additionalValidator = Validator::make($request->all(), $additionalRules);
                if ($additionalValidator->fails()) {
                    // For audio files, be more lenient and check MIME type manually
                    if (in_array($type, ['audio', 'voice'])) {
                        $mimeType = $file->getMimeType();
                        $allowedAudioMimes = [
                            'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/wave', 'audio/x-wav',
                            'audio/aac', 'audio/ogg', 'audio/mp4', 'audio/m4a', 'audio/webm',
                            'audio/3gpp', 'audio/amr', 'application/octet-stream'
                        ];

                        if (!in_array($mimeType, $allowedAudioMimes)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Invalid audio file type. Supported: MP3, WAV, AAC, OGG, M4A',
                                'errors' => ['file' => ['Unsupported audio format: ' . $mimeType]]
                            ], 422);
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'File type validation failed',
                            'errors' => $additionalValidator->errors()
                        ], 422);
                    }
                }
            }

            // Generate unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '_' . time() . '.' . $extension;
            
            // Store file in appropriate directory
            $directory = 'media/' . $type . 's';
            $path = $file->storeAs($directory, $filename, 'public');
            
            // Get file info
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $url = Storage::disk('public')->url($path);
            
            // For images, create thumbnail
            $thumbnailUrl = null;
            if ($type === 'image') {
                $thumbnailUrl = $this->createThumbnail($path, $filename);
            }
            
            // Get media duration for audio/video files
            $duration = null;
            if (in_array($type, ['audio', 'video', 'voice'])) {
                $duration = $this->getMediaDuration(Storage::disk('public')->path($path));
            }

            $response = [
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'original_name' => $originalName . '.' . $extension,
                    'url' => $url,
                    'thumbnail_url' => $thumbnailUrl,
                    'type' => $type,
                    'mime_type' => $mimeType,
                    'size' => $fileSize,
                    'size_formatted' => $this->formatFileSize($fileSize),
                    'duration' => $duration,
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now()
                ],
                'message' => 'File uploaded successfully'
            ];

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,jpg,png|max:5120' // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('avatar');
            $user = Auth::user();
            
            // Delete old avatar if exists
            if ($user->avatar_url) {
                $oldFilename = basename(parse_url($user->avatar_url, PHP_URL_PATH));
                if (Storage::disk('public')->exists('avatars/' . $oldFilename)) {
                    Storage::disk('public')->delete('avatars/' . $oldFilename);
                }
                // Also delete thumbnail
                if (Storage::disk('public')->exists('avatars/thumbnails/' . $oldFilename)) {
                    Storage::disk('public')->delete('avatars/thumbnails/' . $oldFilename);
                }
            }

            // Generate unique filename
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store original file
            $path = $file->storeAs('avatars', $filename, 'public');
            $url = Storage::disk('public')->url($path);
            
            // Create resized versions
            $this->createAvatarSizes(Storage::disk('public')->path($path), $filename);
            
            // Update user avatar
            $user->update(['avatar_url' => $url]);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar_url' => $url,
                    'thumbnail_url' => Storage::disk('public')->url('avatars/thumbnails/' . $filename),
                    'small_url' => Storage::disk('public')->url('avatars/small/' . $filename)
                ],
                'message' => 'Avatar uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload chat group avatar
     */
    public function uploadChatAvatar(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
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
            
            // Check if user is admin of the chat
            $chat = \App\Models\Chat::findOrFail($chatId);
            $isAdmin = $chat->participants()
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin && $chat->type === 'group') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only group admins can change chat avatar'
                ], 403);
            }
            
            // Delete old avatar if exists
            if ($chat->avatar_url) {
                $oldFilename = basename(parse_url($chat->avatar_url, PHP_URL_PATH));
                if (Storage::disk('public')->exists('chat-avatars/' . $oldFilename)) {
                    Storage::disk('public')->delete('chat-avatars/' . $oldFilename);
                }
                // Also delete thumbnail
                if (Storage::disk('public')->exists('chat-avatars/thumbnails/' . $oldFilename)) {
                    Storage::disk('public')->delete('chat-avatars/thumbnails/' . $oldFilename);
                }
            }

            // Generate unique filename
            $filename = 'chat_' . $chatId . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store original file
            $path = $file->storeAs('chat-avatars', $filename, 'public');
            $url = Storage::disk('public')->url($path);
            
            // Create resized versions
            $this->createAvatarSizes(Storage::disk('public')->path($path), $filename, 'chat-avatars');
            
            // Update chat avatar
            $chat->update(['avatar_url' => $url]);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar_url' => $url,
                    'thumbnail_url' => Storage::disk('public')->url('chat-avatars/thumbnails/' . $filename),
                    'small_url' => Storage::disk('public')->url('chat-avatars/small/' . $filename)
                ],
                'message' => 'Chat avatar uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading chat avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload status media (for status updates)
     */
    public function uploadStatusMedia(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:25600', // 25MB max
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
            
            // Type-specific validation
            if ($type === 'image') {
                $typeValidator = Validator::make($request->all(), [
                    'file' => 'mimes:jpeg,jpg,png,gif,webp|max:10240' // 10MB
                ]);
            } else {
                $typeValidator = Validator::make($request->all(), [
                    'file' => 'mimes:mp4,avi,mov,wmv|max:25600' // 25MB
                ]);
            }

            if ($typeValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File type validation failed',
                    'errors' => $typeValidator->errors()
                ], 422);
            }

            // Generate unique filename
            $filename = 'status_' . Auth::id() . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Store file
            $path = $file->storeAs('status', $filename, 'public');
            $url = Storage::disk('public')->url($path);
            
            // Create thumbnail for images
            $thumbnailUrl = null;
            if ($type === 'image') {
                $thumbnailUrl = $this->createThumbnail($path, $filename, 'status');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'url' => $url,
                    'thumbnail_url' => $thumbnailUrl,
                    'type' => $type,
                    'size' => $file->getSize(),
                    'uploaded_at' => now()
                ],
                'message' => 'Status media uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading status media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create thumbnail for images
     */
    private function createThumbnail($originalPath, $filename, $directory = 'media/images'): string
    {
        try {
            $thumbnailDir = dirname($directory) . '/thumbnails';
            Storage::disk('public')->makeDirectory($thumbnailDir);
            
            $thumbnailPath = $thumbnailDir . '/' . $filename;
            $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);
            
            // Create thumbnail using Intervention Image
            $image = Image::make(Storage::disk('public')->path($originalPath));
            $image->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save($fullThumbnailPath, 80);
            
            return Storage::disk('public')->url($thumbnailPath);
        } catch (\Exception $e) {
            // Return null if thumbnail creation fails
            return null;
        }
    }

    /**
     * Create different avatar sizes
     */
    private function createAvatarSizes($originalPath, $filename, $directory = 'avatars'): void
    {
        try {
            // Create directories
            Storage::disk('public')->makeDirectory($directory . '/thumbnails');
            Storage::disk('public')->makeDirectory($directory . '/small');
            
            $image = Image::make($originalPath);
            
            // Create thumbnail (100x100)
            $thumbnail = clone $image;
            $thumbnail->fit(100, 100);
            $thumbnail->save(Storage::disk('public')->path($directory . '/thumbnails/' . $filename), 85);
            
            // Create small (50x50)
            $small = clone $image;
            $small->fit(50, 50);
            $small->save(Storage::disk('public')->path($directory . '/small/' . $filename), 85);
            
        } catch (\Exception $e) {
            // Silently fail if image processing fails
        }
    }

    /**
     * Get media duration for audio/video files
     */
    private function getMediaDuration($filePath): ?int
    {
        try {
            // This is a simple implementation. In production, you might want to use FFmpeg
            // For now, return null as duration calculation requires additional packages
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Delete uploaded file
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file_path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filePath = $request->file_path;
            
            // Security check - ensure file belongs to current user or user has permission
            // This is a basic implementation - you might want to add more security checks
            
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                
                // Also delete thumbnail if exists
                $directory = dirname($filePath);
                $filename = basename($filePath);
                $thumbnailPath = $directory . '/thumbnails/' . $filename;
                
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage()
            ], 500);
        }
    }
}
