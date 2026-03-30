<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebSocketService;

class CleanupWebSocketConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up stale WebSocket connections';

    /**
     * Execute the console command.
     */
    public function handle(WebSocketService $webSocketService)
    {
        $this->info('Cleaning up stale WebSocket connections...');

        $webSocketService->cleanupStaleConnections();

        $this->info('WebSocket connections cleanup completed.');

        return 0;
    }
}
