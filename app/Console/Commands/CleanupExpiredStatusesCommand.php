<?php

namespace App\Console\Commands;

use App\Jobs\CleanupExpiredStatuses;
use Illuminate\Console\Command;

class CleanupExpiredStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired statuses and their media files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting status cleanup...');

        // Dispatch the cleanup job
        CleanupExpiredStatuses::dispatch();

        $this->info('Status cleanup job dispatched successfully.');

        return 0;
    }
}
