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

        // Handle case where $value is already a full URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            // Check if it's a Cloudinary URL (always return as-is)
            if (str_contains($value, 'cloudinary.com')) {
                return $value;
            }

            // Check if it's a local/development URL that needs normalization
            // If it contains localhost, 127.0.0.1, or doesn't match current APP_URL domain
            $appUrl = config('app.url');
            $currentHost = parse_url($appUrl, PHP_URL_HOST);
            $valueHost = parse_url($value, PHP_URL_HOST);

            if ($valueHost === 'localhost' || $valueHost === '127.0.0.1' || ($currentHost && $valueHost !== $currentHost)) {
                // Extract the path and re-generate using current APP_URL
                $path = parse_url($value, PHP_URL_PATH);
                if ($path) {
                    // Remove leading /storage/ if present to avoid double prefixing
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    return URL::to('/storage/' . ltrim($cleanPath, '/'));
                }
            }

            return $value;
        }

        // Paths starting with storage (relative or absolute)
        $path = ltrim($value, '/');
        if (str_starts_with($path, 'storage/')) {
            return URL::to('/' . $path);
        }

        // Local storage paths like "avatars/xxx", "statuses/xxx", "messages/xxx", etc.
        // These are stored in storage/app/public and served from /storage/
        if (preg_match('#^(avatars|statuses|chat-avatars|group_avatars|messages|media|logos)/.+#', $path)) {
            return URL::to('/storage/' . $path);
        }

        // Fallback: assume it's a path under storage
        if (!str_contains($path, '://')) {
            return URL::to('/storage/' . $path);
        }

        return $value;
    }
}
