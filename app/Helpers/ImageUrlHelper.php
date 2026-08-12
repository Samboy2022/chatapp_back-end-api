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

            // Re-point a URL that is one of *our own* stored files but carries
            // a stale host — a row written on localhost, or before the domain
            // changed.
            //
            // The test is deliberately narrow: only URLs whose path is under
            // /storage/ are ours to rewrite. Matching on "host differs from
            // APP_URL" alone, as this once did, also caught every legitimate
            // external image — an Unsplash or CDN URL was rewritten to
            // https://our-app/storage/photo-abc123 and 404'd.
            $appUrl = config('app.url');
            $currentHost = parse_url($appUrl, PHP_URL_HOST);
            $valueHost = parse_url($value, PHP_URL_HOST);
            $valuePath = parse_url($value, PHP_URL_PATH) ?: '';

            $isOurStoragePath = (bool) preg_match('#^/?storage/#', $valuePath);
            $isLocalDevHost = in_array($valueHost, ['localhost', '127.0.0.1', '::1'], true)
                || str_ends_with((string) $valueHost, '.test')
                || str_ends_with((string) $valueHost, '.local');

            $hostIsForeign = $currentHost && $valueHost !== $currentHost;

            if ($isOurStoragePath && ($isLocalDevHost || $hostIsForeign)) {
                // Strip the /storage/ prefix so re-adding it can't double up.
                $cleanPath = preg_replace('/^\/?storage\//', '', $valuePath);
                return URL::to('/storage/' . ltrim($cleanPath, '/'));
            }

            // Anything else absolute is left exactly as the admin entered it.
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
