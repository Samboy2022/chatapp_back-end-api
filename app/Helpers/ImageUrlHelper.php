<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Helper to ensure image URLs are always full/absolute for deployment.
 * Fixes images not showing when API is deployed (mobile apps need full URLs).
 */
class ImageUrlHelper
{
    /**
     * Convert any image path or URL to a full absolute URL.
     * Handles Cloudinary URLs, local storage paths, and relative paths.
     *
     * @param string|null $value The stored URL or path
     * @return string|null Full absolute URL or null
     */
    public static function fullUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Already a full URL (Cloudinary, S3, etc.) - return as-is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Paths starting with storage (relative or absolute)
        $path = ltrim($value, '/');
        if (str_starts_with($path, 'storage/')) {
            return URL::to('/' . $path);
        }

        // Local storage paths like "avatars/xxx", "statuses/xxx", "messages/xxx", etc.
        // These are stored in storage/app/public and served from /storage/
        if (preg_match('#^(avatars|statuses|chat-avatars|group_avatars|messages|media)/.+#', $path)) {
            return URL::to('/storage/' . $path);
        }

        // Fallback: assume it's a path under storage
        if (!str_contains($path, '://')) {
            return URL::to('/storage/' . $path);
        }

        return $value;
    }
}
