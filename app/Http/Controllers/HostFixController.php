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
        $publicStoragePath = public_path('storage');
        $storageAppPublic = storage_path('app/public');
        
        return response()->json([
            'success' => true,
            'data' => [
                'app_url' => config('app.url'),
                'env_app_url' => env('APP_URL'),
                'storage_link_exists' => file_exists($publicStoragePath),
                'storage_is_link' => is_link($publicStoragePath),
                'storage_target_matches' => is_link($publicStoragePath) && readlink($publicStoragePath) === $storageAppPublic,
                'public_path' => public_path(),
                'storage_path' => storage_path(),
                'public_writable' => is_writable(public_path()),
                'storage_writable' => is_writable(storage_path('app/public')),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ]);
    }

    /**
     * Normalize all existing URLs in the database.
     * Converts full local URLs (localhost/127.0.0.1) to relative paths.
     */
    public function normalizeUrls()
    {
        try {
            $results = [];

            // 1. Fix Users table (avatars)
            $users = \App\Models\User::where('avatar_url', 'LIKE', 'http%')->get();
            $userCount = 0;
            foreach ($users as $user) {
                $path = $this->extractRelativePath($user->avatar_url);
                if ($path !== $user->avatar_url) {
                    $user->avatar_url = $path;
                    $user->save();
                    $userCount++;
                }
            }
            $results['users_fixed'] = $userCount;

            // 2. Fix Statuses table (media_url)
            $statuses = \App\Models\Status::where('media_url', 'LIKE', 'http%')->get();
            $statusCount = 0;
            foreach ($statuses as $status) {
                $path = $this->extractRelativePath($status->media_url);
                if ($path !== $status->media_url) {
                    $status->media_url = $path;
                    $status->save();
                    $statusCount++;
                }
            }
            $results['statuses_fixed'] = $statusCount;

            // 3. Fix Messages table (media_url)
            $messages = \App\Models\Message::where('media_url', 'LIKE', 'http%')->get();
            $messageCount = 0;
            foreach ($messages as $message) {
                $path = $this->extractRelativePath($message->media_url);
                if ($path !== $message->media_url) {
                    $message->media_url = $path;
                    $message->save();
                    $messageCount++;
                }
            }
            $results['messages_fixed'] = $messageCount;

            // 4. Fix Settings table (logo_url)
            $settings = \App\Models\Setting::where('key', 'logo_url')->where('value', 'LIKE', 'http%')->get();
            $settingCount = 0;
            foreach ($settings as $setting) {
                $path = $this->extractRelativePath($setting->value);
                if ($path !== $setting->value) {
                    $setting->value = $path;
                    $setting->save();
                    $settingCount++;
                }
            }
            $results['settings_fixed'] = $settingCount;

            return response()->json([
                'success' => true,
                'message' => 'Database URLs normalized successfully.',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract relative path from a full URL if it points to local storage.
     */
    private function extractRelativePath($url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // If it's a Cloudinary URL, keep it
        if (str_contains($url, 'cloudinary.com')) {
            return $url;
        }

        // Extract path
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            // Remove /storage/ prefix
            return preg_replace('/^\/?storage\//', '', $path);
        }

        return $url;
    }

    /**
     * Test image upload functionality.
     */
    public function testUpload()
    {
        try {
            $testDir = storage_path('app/public/test');
            if (!file_exists($testDir)) {
                mkdir($testDir, 0755, true);
            }

            $filename = 'test_' . time() . '.txt';
            $filepath = $testDir . '/' . $filename;
            
            file_put_contents($filepath, 'Storage test at ' . now());
            
            $publicUrl = asset('storage/test/' . $filename);
            
            return response()->json([
                'success' => true,
                'message' => 'Storage write test successful.',
                'file_created' => $filepath,
                'public_url' => $publicUrl,
                'note' => 'Try visiting the public_url to see if it works.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Storage test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
