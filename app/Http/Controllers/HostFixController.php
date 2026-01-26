<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class HostFixController extends Controller
{
    /**
     * Regenerate the storage symlink safely for shared hosting.
     */
    public function fixSymlink()
    {
        $publicStoragePath = public_path('storage');
        $targetStoragePath = storage_path('app/public');

        try {
            // Remove existing storage link if it exists and is a link or a directory
            if (file_exists($publicStoragePath)) {
                if (is_link($publicStoragePath)) {
                    unlink($publicStoragePath);
                } else if (is_dir($publicStoragePath)) {
                    File::deleteDirectory($publicStoragePath);
                }
            }

            // Create the symlink using PHP's symlink function
            if (symlink($targetStoragePath, $publicStoragePath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Storage symlink created successfully.',
                    'path' => $publicStoragePath,
                    'target' => $targetStoragePath
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create symlink using symlink().'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update APP_URL in .env file.
     */
    public function updateAppUrl(Request $request)
    {
        $newUrl = $request->query('url', 'https://fnskills.ng');
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return response()->json([
                'success' => false,
                'message' => '.env file not found at ' . $envPath
            ], 404);
        }

        try {
            $content = file_get_contents($envPath);
            
            // Match APP_URL line
            $pattern = '/^APP_URL=(.*)$/m';
            
            if (preg_match($pattern, $content)) {
                $newContent = preg_replace($pattern, 'APP_URL=' . $newUrl, $content);
            } else {
                $newContent = $content . "\nAPP_URL=" . $newUrl;
            }

            if (file_put_contents($envPath, $newContent) !== false) {
                // Clear config cache to apply changes
                Artisan::call('config:clear');
                
                return response()->json([
                    'success' => true,
                    'message' => 'APP_URL updated to ' . $newUrl,
                    'note' => 'Config cache has been cleared.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to write to .env file.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Comprehensive host check.
     */
    public function checkStatus()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'app_url' => config('app.url'),
                'env_app_url' => env('APP_URL'),
                'storage_link_exists' => file_exists(public_path('storage')),
                'storage_is_link' => is_link(public_path('storage')),
                'public_path' => public_path(),
                'storage_path' => storage_path(),
                'env_path' => base_path('.env'),
                'env_writable' => is_writable(base_path('.env')),
                'public_writable' => is_writable(public_path())
            ]
        ]);
    }
}
