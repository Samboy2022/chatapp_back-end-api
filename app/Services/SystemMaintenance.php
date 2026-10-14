<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * What Settings → Maintenance runs: migrations, caches, backups, queue,
 * maintenance mode. Lets an admin on shared hosting (no SSH) do the jobs
 * that would otherwise need `php artisan`.
 *
 * Deliberately absent: migrate:fresh / reset / wipe (they delete every
 * table) and config:cache (code in this app still reads env() at runtime,
 * which returns null once config is cached — OTP SMS, push, uploads and
 * calls would all stop).
 */
class SystemMaintenance
{
    public function __construct(private DatabaseBackupService $backups)
    {
    }

    // ── Migrations ──────────────────────────────────────────────────────────

    /**
     * Every migration file with whether it has run, and in which batch.
     *
     * @return array{installed: bool, migrations: array<int, array{name: string, ran: bool, batch: ?int}>, pending: array<int, string>}
     */
    public function migrationStatus(): array
    {
        $migrator = app('migrator');
        $repository = $migrator->getRepository();
        $files = $migrator->getMigrationFiles(
            array_merge([database_path('migrations')], $migrator->paths())
        );

        $installed = $repository->repositoryExists();
        $batches = $installed ? $repository->getMigrationBatches() : [];

        $migrations = [];
        foreach (array_keys($files) as $name) {
            $migrations[] = [
                'name' => $name,
                'ran' => array_key_exists($name, $batches),
                'batch' => $batches[$name] ?? null,
            ];
        }

        return [
            'installed' => $installed,
            'migrations' => $migrations,
            'pending' => array_values(array_map(
                fn ($m) => $m['name'],
                array_filter($migrations, fn ($m) => !$m['ran'])
            )),
        ];
    }

    /**
     * The [$steps] most recently run migrations — what a rollback of that
     * many steps would undo, newest first.
     *
     * @return array<int, string>
     */
    public function rollbackPreview(int $steps): array
    {
        $repository = app('migrator')->getRepository();
        if (!$repository->repositoryExists()) {
            return [];
        }
        return array_map(fn ($m) => $m->migration, $repository->getMigrations($steps));
    }

    /** The SQL pending migrations would run, without running it. */
    public function pretendMigrate(): string
    {
        return $this->artisan('migrate', ['--pretend' => true, '--force' => true]);
    }

    /**
     * Run pending migrations. Unless [$withBackup] is false ("force"), a
     * backup is taken first and nothing runs if the backup fails.
     *
     * @return array{output: string, backup: ?string}
     */
    public function migrate(bool $withBackup = true): array
    {
        return $this->exclusively(function () use ($withBackup) {
            $backup = $withBackup ? $this->backups->create('pre-migrate')['filename'] : null;
            // --step gives each migration its own batch, so "roll back 1"
            // undoes exactly one migration rather than a whole deploy.
            $output = $this->artisan('migrate', ['--force' => true, '--step' => true]);
            return ['output' => $output, 'backup' => $backup];
        });
    }

    /**
     * Undo the last [$steps] migrations, after a backup.
     *
     * @return array{output: string, backup: string}
     */
    public function rollback(int $steps): array
    {
        $steps = max(1, min($steps, 20));

        return $this->exclusively(function () use ($steps) {
            $backup = $this->backups->create('pre-rollback')['filename'];
            $output = $this->artisan('migrate:rollback', ['--force' => true, '--step' => $steps]);
            return ['output' => $output, 'backup' => $backup];
        });
    }

    // ── Caches ──────────────────────────────────────────────────────────────

    public const CACHES = [
        'application' => 'cache:clear',
        'config' => 'config:clear',
        'routes' => 'route:clear',
        'views' => 'view:clear',
        'events' => 'event:clear',
    ];

    /** Clear one cache by key in [CACHES], or every cache with 'all'. */
    public function clearCache(string $which): string
    {
        if ($which === 'all') {
            $output = $this->artisan('optimize:clear');
            \App\Models\Setting::clearCache();
            return $output . "\nSettings cache cleared.";
        }
        if (!isset(self::CACHES[$which])) {
            throw new \InvalidArgumentException("Unknown cache: {$which}");
        }
        $output = $this->artisan(self::CACHES[$which]);
        if ($which === 'application') {
            \App\Models\Setting::clearCache();
        }
        return $output;
    }

    /**
     * Speed-up caches that are safe for this codebase: routes, views and
     * events — never config (see the class comment). Each step is
     * independent, so one failing doesn't block the others.
     */
    public static function cacheSafely(): string
    {
        Artisan::call('config:clear');
        $log = [];
        foreach (['route:cache', 'view:cache', 'event:cache'] as $command) {
            try {
                Artisan::call($command);
                $log[] = "✓ {$command}";
            } catch (\Throwable $e) {
                $log[] = "✗ {$command}: " . $e->getMessage();
            }
        }
        return implode("\n", $log);
    }

    // ── Backups ─────────────────────────────────────────────────────────────

    public function backups(): DatabaseBackupService
    {
        return $this->backups;
    }

    // ── Queue ───────────────────────────────────────────────────────────────

    public function retryFailedJobs(): string
    {
        return $this->artisan('queue:retry', ['id' => ['all']]);
    }

    public function restartQueueWorkers(): string
    {
        return $this->artisan('queue:restart');
    }

    // ── Maintenance mode ────────────────────────────────────────────────────

    /**
     * Put the site into maintenance mode. Returns the secret path that lets
     * the admin keep browsing while everyone else sees the 503 page.
     */
    public function down(): string
    {
        $secret = Str::random(32);
        $this->artisan('down', ['--secret' => $secret, '--retry' => 60]);
        return $secret;
    }

    public function up(): string
    {
        return $this->artisan('up');
    }

    // ── Storage & logs ──────────────────────────────────────────────────────

    public function linkStorage(): string
    {
        if (is_link(public_path('storage'))) {
            return 'public/storage is already linked.';
        }
        return $this->artisan('storage:link');
    }

    public function logPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    /** The last [$lines] lines of the application log. */
    public function tailLog(int $lines = 200): string
    {
        $path = $this->logPath();
        if (!is_file($path)) {
            return '';
        }
        // Read only the end: the log here is megabytes.
        $handle = fopen($path, 'rb');
        $size = filesize($path);
        $chunk = min($size, 256 * 1024);
        fseek($handle, -$chunk, SEEK_END);
        $text = (string) fread($handle, $chunk);
        fclose($handle);

        return implode("\n", array_slice(explode("\n", rtrim($text)), -$lines));
    }

    public function clearLog(): void
    {
        if (is_file($this->logPath())) {
            file_put_contents($this->logPath(), '');
        }
    }

    // ── Overview ────────────────────────────────────────────────────────────

    /** Health facts for the top of the page. */
    public function overview(): array
    {
        $databaseOk = true;
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $databaseOk = false;
        }

        $backups = $this->backups->all();
        $free = @disk_free_space(base_path());

        return [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'database_ok' => $databaseOk,
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'config_cached' => app()->configurationIsCached(),
            'routes_cached' => app()->routesAreCached(),
            'down' => app()->isDownForMaintenance(),
            'storage_linked' => is_link(public_path('storage')),
            'storage_writable' => is_writable(storage_path()),
            'disk_free' => $free === false ? null : (int) $free,
            'pending_migrations' => $databaseOk ? count($this->migrationStatus()['pending']) : null,
            'failed_jobs' => $databaseOk && Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
            'queued_jobs' => $databaseOk && Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
            'last_backup' => $backups[0] ?? null,
            'agora_configured' => app(AgoraService::class)->isConfigured(),
            'log_size' => is_file($this->logPath()) ? filesize($this->logPath()) : 0,
        ];
    }

    // ── Internals ───────────────────────────────────────────────────────────

    private function artisan(string $command, array $parameters = []): string
    {
        @set_time_limit(300);
        $code = Artisan::call($command, $parameters);
        $output = trim(Artisan::output());
        if ($code !== 0) {
            throw new \RuntimeException($output !== '' ? $output : "{$command} failed (exit {$code})");
        }
        return $output !== '' ? $output : 'Done.';
    }

    /**
     * Run [$work] while no other maintenance job is running — two admins
     * migrating at once would corrupt the migrations table.
     */
    private function exclusively(callable $work)
    {
        $lock = Cache::lock('admin-maintenance', 600);
        if (!$lock->get()) {
            throw new \RuntimeException('Another maintenance task is already running. Try again in a minute.');
        }
        try {
            return $work();
        } finally {
            $lock->release();
        }
    }
}
