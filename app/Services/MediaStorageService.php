<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Stores admin-uploaded media (radio audio, thumbnails).
 *
 * Prefers Cloudinary when credentials are configured, and falls back to the
 * local `public` disk otherwise — so a developer box with no Cloudinary keys
 * still works without any code change.
 *
 * Every method returns the same shape so callers don't have to care which
 * backend served the request:
 *
 *   ['success' => bool, 'url' => string, 'disk' => string,
 *    'public_id' => ?string, 'path' => ?string, 'bytes' => int, 'error' => ?string]
 */
class MediaStorageService
{
    public function __construct(private ?CloudinaryService $cloudinary = null)
    {
    }

    public function cloudinaryConfigured(): bool
    {
        return filled(env('CLOUDINARY_CLOUD_NAME'))
            && filled(env('CLOUDINARY_API_KEY'))
            && filled(env('CLOUDINARY_API_SECRET'));
    }

    public function putAudio(UploadedFile $file, string $folder = 'radio/audio'): array
    {
        return $this->put($file, $folder, isAudio: true);
    }

    public function putImage(UploadedFile $file, string $folder = 'radio/thumbnails'): array
    {
        return $this->put($file, $folder, isAudio: false);
    }

    private function put(UploadedFile $file, string $folder, bool $isAudio): array
    {
        if ($this->cloudinaryConfigured()) {
            $service = $this->cloudinary ?? new CloudinaryService();

            $result = $isAudio
                ? $service->uploadAudio($file, $folder)
                : $service->uploadImage($file, $folder, false);

            if (($result['success'] ?? false) && filled($result['url'] ?? null)) {
                return [
                    'success' => true,
                    'url' => $result['url'],
                    'disk' => 'cloudinary',
                    'public_id' => $result['public_id'] ?? null,
                    'path' => null,
                    'bytes' => $result['bytes'] ?? $file->getSize(),
                    'error' => null,
                ];
            }

            // Don't fail the upload outright — fall through to local storage so
            // the admin still gets a working file.
            Log::warning('Cloudinary upload failed, storing locally instead', [
                'error' => $result['error'] ?? 'unknown',
            ]);
        }

        return $this->putLocally($file, $folder);
    }

    private function putLocally(UploadedFile $file, string $folder): array
    {
        try {
            $path = $file->store($folder, 'public');

            return [
                'success' => true,
                'url' => Storage::disk('public')->url($path),
                'disk' => 'public',
                'public_id' => null,
                'path' => $path,
                'bytes' => $file->getSize(),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Local media store failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'url' => null,
                'disk' => null,
                'public_id' => null,
                'path' => null,
                'bytes' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove a previously stored file. Best-effort: a failed delete should
     * never block the record update that triggered it.
     */
    public function forget(?string $disk, ?string $publicId, ?string $path, bool $isAudio = false): void
    {
        try {
            if ($disk === 'cloudinary' && filled($publicId)) {
                $service = $this->cloudinary ?? new CloudinaryService();
                $service->delete($publicId, $isAudio ? 'video' : 'image');
                return;
            }

            if ($disk === 'public' && filled($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('Media delete failed', ['error' => $e->getMessage()]);
        }
    }
}
