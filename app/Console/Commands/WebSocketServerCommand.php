<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebSocketServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve 
                            {--host=0.0.0.0 : The host to serve on}
                            {--port=8080 : The port to serve on}
                            {--debug : Enable debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server using Laravel Reverb';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');
        $debug = $this->option('debug');

        $this->info("Starting WebSocket server...");
        $this->info("Host: {$host}");
        $this->info("Port: {$port}");
        
        if ($debug) {
            $this->info("Debug mode: enabled");
        }

        // Store server start time
        Cache::put('websocket_server_start_time', now(), now()->addDays(1));

        // Build the reverb command
        $command = [
            'php',
            'artisan',
            'reverb:start',
            '--host=' . $host,
            '--port=' . $port,
        ];

        if ($debug) {
            $command[] = '--debug';
        }

        $this->info("Executing: " . implode(' ', $command));

        // Execute the reverb command
        $process = proc_open(
            implode(' ', $command),
            [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ],
            $pipes
        );

        if (is_resource($process)) {
            // Close stdin
            fclose($pipes[0]);

            // Read output in real-time
            while (!feof($pipes[1])) {
                $output = fgets($pipes[1]);
                if ($output) {
                    $this->line(trim($output));
                }
            }

            // Read errors
            while (!feof($pipes[2])) {
                $error = fgets($pipes[2]);
                if ($error) {
                    $this->error(trim($error));
                }
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            if ($returnCode === 0) {
                $this->info("WebSocket server stopped gracefully.");
            } else {
                $this->error("WebSocket server stopped with error code: {$returnCode}");
            }
        } else {
            $this->error("Failed to start WebSocket server process.");
            return 1;
        }

        return 0;
    }
}
