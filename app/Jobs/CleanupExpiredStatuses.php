<?php

namespace App\Jobs;

use App\Models\Status;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupExpiredStatuses implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting cleanup of expired statuses');

        try {
            // Get all expired statuses
            $expiredStatuses = Status::where('expires_at', '<=', Carbon::now())
                ->get();

            $deletedCount = 0;
            $mediaDeletedCount = 0;

            foreach ($expiredStatuses as $status) {
                // Delete associated media files
                if ($status->media_url && Storage::disk('public')->exists($status->media_url)) {
                    Storage::disk('public')->delete($status->media_url);
                    $mediaDeletedCount++;
                }

                // Delete thumbnail if exists
                if ($status->thumbnail_url && Storage::disk('public')->exists($status->thumbnail_url)) {
                    Storage::disk('public')->delete($status->thumbnail_url);
                }

                // Delete the status (this will cascade delete views)
                $status->delete();
                $deletedCount++;
            }

            Log::info("Cleanup completed: {$deletedCount} statuses deleted, {$mediaDeletedCount} media files removed");

        } catch (\Exception $e) {
            Log::error('Error during status cleanup: ' . $e->getMessage());
            throw $e;
        }
    }
}
