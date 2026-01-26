<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloudinaryService;
use App\Models\User;
use App\Models\Status;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MigrateImagesToCloudinary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:migrate 
                            {--type=all : Type of images to migrate (all, avatars, statuses, chats, messages, logos)}
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local storage images to Cloudinary';

    protected $cloudinary;
    protected $dryRun = false;
    protected $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->cloudinary = app(CloudinaryService::class);
        $this->dryRun = $this->option('dry-run');
        $type = $this->option('type');

        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        if (!$this->option('force')) {
            if (!$this->confirm('This will migrate images from local storage to Cloudinary. Continue?')) {
                $this->info('Migration cancelled.');
                return 0;
            }
        }

        $this->info('🚀 Starting image migration to Cloudinary...');
        $this->newLine();

        switch ($type) {
            case 'avatars':
                $this->migrateAvatars();
                break;
            case 'statuses':
                $this->migrateStatuses();
                break;
            case 'chats':
                $this->migrateChatAvatars();
                break;
            case 'messages':
                $this->migrateMessageMedia();
                break;
            case 'logos':
                $this->migrateLogos();
                break;
            case 'all':
            default:
                $this->migrateAvatars();
                $this->migrateStatuses();
                $this->migrateChatAvatars();
                $this->migrateMessageMedia();
                $this->migrateLogos();
                break;
        }

        $this->newLine();
        $this->displayStats();

        return 0;
    }

    /**
     * Migrate user avatars
     */
    protected function migrateAvatars()
    {
        $this->info('📸 Migrating user avatars...');

        $users = User::whereNotNull('avatar_url')
            ->where('avatar_url', '!=', '')
            ->where('avatar_url', 'not like', '%cloudinary.com%')
            ->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->stats['total']++;
            
            try {
                $localPath = $this->extractLocalPath($user->avatar_url);
                
                if (!$localPath || !Storage::disk('public')->exists($localPath)) {
                    $this->stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                if (!$this->dryRun) {
                    $fullPath = Storage::disk('public')->path($localPath);
                    $file = new \Illuminate\Http\UploadedFile($fullPath, basename($localPath));
                    
                    $result = $this->cloudinary->uploadAvatar($file, $user->id);
                    
                    if ($result['success']) {
                        $user->avatar_url = $result['avatar_url'];
                        $user->save();
                        
                        // Delete local file
                        Storage::disk('public')->delete($localPath);
                        
                        $this->stats['success']++;
                    } else {
                        $this->stats['failed']++;
                        Log::error("Failed to migrate avatar for user {$user->id}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->stats['success']++;
                }
                
            } catch (\Exception $e) {
                $this->stats['failed']++;
                Log::error("Error migrating avatar for user {$user->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migrate status media
     */
    protected function migrateStatuses()
    {
        $this->info('📱 Migrating status media...');

        $statuses = Status::whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->where('media_url', 'not like', '%cloudinary.com%')
            ->get();

        $bar = $this->output->createProgressBar($statuses->count());
        $bar->start();

        foreach ($statuses as $status) {
            $this->stats['total']++;
            
            try {
                $localPath = $this->extractLocalPath($status->media_url);
                
                if (!$localPath || !Storage::disk('public')->exists($localPath)) {
                    $this->stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                if (!$this->dryRun) {
                    $fullPath = Storage::disk('public')->path($localPath);
                    $file = new \Illuminate\Http\UploadedFile($fullPath, basename($localPath));
                    
                    $result = $status->content_type === 'video'
                        ? $this->cloudinary->uploadVideo($file, 'status')
                        : $this->cloudinary->uploadImage($file, 'status', true);
                    
                    if ($result['success']) {
                        $status->media_url = $result['url'];
                        if (isset($result['thumbnail_url'])) {
                            $status->thumbnail_url = $result['thumbnail_url'];
                        }
                        $status->save();
                        
                        // Delete local file
                        Storage::disk('public')->delete($localPath);
                        
                        $this->stats['success']++;
                    } else {
                        $this->stats['failed']++;
                        Log::error("Failed to migrate status {$status->id}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->stats['success']++;
                }
                
            } catch (\Exception $e) {
                $this->stats['failed']++;
                Log::error("Error migrating status {$status->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migrate chat avatars
     */
    protected function migrateChatAvatars()
    {
        $this->info('💬 Migrating chat avatars...');

        $chats = Chat::whereNotNull('avatar_url')
            ->where('avatar_url', '!=', '')
            ->where('avatar_url', 'not like', '%cloudinary.com%')
            ->get();

        $bar = $this->output->createProgressBar($chats->count());
        $bar->start();

        foreach ($chats as $chat) {
            $this->stats['total']++;
            
            try {
                $localPath = $this->extractLocalPath($chat->avatar_url);
                
                if (!$localPath || !Storage::disk('public')->exists($localPath)) {
                    $this->stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                if (!$this->dryRun) {
                    $fullPath = Storage::disk('public')->path($localPath);
                    $file = new \Illuminate\Http\UploadedFile($fullPath, basename($localPath));
                    
                    $result = $this->cloudinary->uploadImage($file, 'chat-avatars', true);
                    
                    if ($result['success']) {
                        $chat->avatar_url = $result['url'];
                        $chat->save();
                        
                        // Delete local file
                        Storage::disk('public')->delete($localPath);
                        
                        $this->stats['success']++;
                    } else {
                        $this->stats['failed']++;
                        Log::error("Failed to migrate chat avatar {$chat->id}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->stats['success']++;
                }
                
            } catch (\Exception $e) {
                $this->stats['failed']++;
                Log::error("Error migrating chat avatar {$chat->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migrate message media
     */
    protected function migrateMessageMedia()
    {
        $this->info('💌 Migrating message media...');

        $messages = Message::whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->where('media_url', 'not like', '%cloudinary.com%')
            ->whereIn('message_type', ['image', 'video', 'audio', 'document'])
            ->get();

        $bar = $this->output->createProgressBar($messages->count());
        $bar->start();

        foreach ($messages as $message) {
            $this->stats['total']++;
            
            try {
                $localPath = $this->extractLocalPath($message->media_url);
                
                if (!$localPath || !Storage::disk('public')->exists($localPath)) {
                    $this->stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                if (!$this->dryRun) {
                    $fullPath = Storage::disk('public')->path($localPath);
                    $file = new \Illuminate\Http\UploadedFile($fullPath, basename($localPath));
                    
                    $result = null;
                    switch ($message->message_type) {
                        case 'image':
                            $result = $this->cloudinary->uploadImage($file, 'media/images', true);
                            break;
                        case 'video':
                            $result = $this->cloudinary->uploadVideo($file, 'media/videos');
                            break;
                        case 'audio':
                            $result = $this->cloudinary->uploadAudio($file, 'media/audios');
                            break;
                        case 'document':
                            $result = $this->cloudinary->uploadDocument($file, 'media/documents');
                            break;
                    }
                    
                    if ($result && $result['success']) {
                        $message->media_url = $result['url'];
                        if (isset($result['thumbnail_url'])) {
                            $message->thumbnail_url = $result['thumbnail_url'];
                        }
                        $message->save();
                        
                        // Delete local file
                        Storage::disk('public')->delete($localPath);
                        
                        $this->stats['success']++;
                    } else {
                        $this->stats['failed']++;
                        Log::error("Failed to migrate message media {$message->id}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->stats['success']++;
                }
                
            } catch (\Exception $e) {
                $this->stats['failed']++;
                Log::error("Error migrating message media {$message->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migrate app logos
     */
    protected function migrateLogos()
    {
        $this->info('🎨 Migrating app logos...');

        $logoUrl = Setting::get('logo_url');
        
        if (!$logoUrl || str_contains($logoUrl, 'cloudinary.com')) {
            $this->info('  No local logo found or already on Cloudinary');
            return;
        }

        $this->stats['total']++;
        
        try {
            $localPath = $this->extractLocalPath($logoUrl);
            
            if (!$localPath || !Storage::disk('public')->exists($localPath)) {
                $this->stats['skipped']++;
                $this->warn('  Logo file not found in storage');
                return;
            }

            if (!$this->dryRun) {
                $fullPath = Storage::disk('public')->path($localPath);
                $file = new \Illuminate\Http\UploadedFile($fullPath, basename($localPath));
                
                $result = $this->cloudinary->uploadLogo($file);
                
                if ($result['success']) {
                    Setting::set('logo_url', $result['logo_url'], 'string', 'general');
                    
                    // Delete local file
                    Storage::disk('public')->delete($localPath);
                    
                    $this->stats['success']++;
                    $this->info('  ✓ Logo migrated successfully');
                } else {
                    $this->stats['failed']++;
                    $this->error('  ✗ Failed to migrate logo: ' . ($result['error'] ?? 'Unknown error'));
                }
            } else {
                $this->stats['success']++;
                $this->info('  ✓ Logo would be migrated');
            }
            
        } catch (\Exception $e) {
            $this->stats['failed']++;
            $this->error('  ✗ Error migrating logo: ' . $e->getMessage());
        }
    }

    /**
     * Extract local path from URL
     */
    protected function extractLocalPath($url)
    {
        if (empty($url)) {
            return null;
        }

        // Remove /storage/ prefix and domain
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        
        return $path;
    }

    /**
     * Display migration statistics
     */
    protected function displayStats()
    {
        $this->info('📊 Migration Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $this->stats['total']],
                ['✓ Successful', $this->stats['success']],
                ['✗ Failed', $this->stats['failed']],
                ['⊘ Skipped', $this->stats['skipped']],
            ]
        );

        if ($this->dryRun) {
            $this->warn('This was a dry run. No actual changes were made.');
        } else {
            $this->info('✅ Migration completed!');
        }
    }
}
