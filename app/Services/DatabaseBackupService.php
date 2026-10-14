<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SQL backups of the app database, kept in storage/app/backups.
 *
 * Uses mysqldump when the host allows it, and otherwise a PHP dump that
 * streams table by table so a large database doesn't exhaust memory.
 */
class DatabaseBackupService
{
    /** Only names this service produced can be downloaded or deleted. */
    private const NAME_PATTERN = '/^backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}(_[a-z0-9-]+)?\.sql$/';

    public function directory(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir;
    }

    /**
     * Write a new backup. [$label] tags why it was taken ("pre-migrate").
     *
     * @return array{filename: string, path: string, method: string, size: int}
     */
    public function create(?string $label = null): array
    {
        $suffix = $label ? '_' . preg_replace('/[^a-z0-9-]/', '', strtolower($label)) : '';
        $filename = 'backup_' . date('Y-m-d_H-i-s') . $suffix . '.sql';
        $path = $this->directory() . DIRECTORY_SEPARATOR . $filename;

        $method = 'php';
        if ($this->mysqldumpAvailable()) {
            try {
                $this->dumpWithMysqldump($path);
                $method = 'mysqldump';
            } catch (\Throwable $e) {
                Log::warning('Backup: mysqldump failed, using PHP dump: ' . $e->getMessage());
                @unlink($path);
            }
        }
        if ($method === 'php') {
            $this->dumpWithPhp($path);
        }

        clearstatcache(true, $path);
        if (!is_file($path) || filesize($path) === 0) {
            throw new \RuntimeException('The backup file is empty.');
        }

        Log::info("Backup: created {$filename} via {$method}");

        return [
            'filename' => $filename,
            'path' => $path,
            'method' => $method,
            'size' => filesize($path),
        ];
    }

    /**
     * Existing backups, newest first.
     *
     * @return array<int, array{filename: string, size: int, created_at: int}>
     */
    public function all(): array
    {
        $files = [];
        foreach (glob($this->directory() . DIRECTORY_SEPARATOR . '*.sql') ?: [] as $path) {
            $files[] = [
                'filename' => basename($path),
                'size' => filesize($path),
                'created_at' => filemtime($path),
            ];
        }
        usort($files, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);
        return $files;
    }

    /** Absolute path of an existing backup, or null for anything else. */
    public function pathFor(string $filename): ?string
    {
        // Older backups used a different prefix; accept those too, but never
        // anything with a path separator in it.
        $legacy = '/^farmersnetwork_backup_[0-9_-]+\.sql$/';
        if (!preg_match(self::NAME_PATTERN, $filename) && !preg_match($legacy, $filename)) {
            return null;
        }
        $path = $this->directory() . DIRECTORY_SEPARATOR . $filename;
        return is_file($path) ? $path : null;
    }

    public function delete(string $filename): bool
    {
        $path = $this->pathFor($filename);
        return $path !== null && unlink($path);
    }

    private function mysqldumpAvailable(): bool
    {
        // Shared hosts often disable exec() outright; calling it then is fatal.
        if (!function_exists('exec') || in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            return false;
        }
        if (config('database.connections.' . config('database.default') . '.driver') !== 'mysql') {
            return false;
        }
        $out = [];
        $code = 1;
        @exec('mysqldump --version 2>/dev/null', $out, $code);
        return $code === 0;
    }

    private function dumpWithMysqldump(string $path): void
    {
        $db = config('database.connections.' . config('database.default'));

        // Credentials go in a private options file, not on the command line,
        // where any user on the host could read them from the process list.
        $options = tempnam(sys_get_temp_dir(), 'dump');
        chmod($options, 0600);
        file_put_contents($options, sprintf(
            "[client]\nuser=\"%s\"\npassword=\"%s\"\nhost=\"%s\"\nport=%d\n",
            addcslashes((string) $db['username'], '"\\'),
            addcslashes((string) ($db['password'] ?? ''), '"\\'),
            addcslashes((string) $db['host'], '"\\'),
            (int) ($db['port'] ?? 3306)
        ));
        $errors = tempnam(sys_get_temp_dir(), 'dumperr');

        try {
            $command = sprintf(
                'mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --no-tablespaces %s > %s 2> %s',
                escapeshellarg($options),
                escapeshellarg((string) $db['database']),
                escapeshellarg($path),
                // stderr kept out of the dump: warnings written into the .sql
                // file used to make the backup impossible to restore.
                escapeshellarg($errors)
            );
            $out = [];
            $code = 1;
            exec($command, $out, $code);

            if ($code !== 0) {
                throw new \RuntimeException(trim((string) file_get_contents($errors)) ?: "exit code {$code}");
            }
        } finally {
            @unlink($options);
            @unlink($errors);
        }
    }

    private function dumpWithPhp(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        $out = fopen($path, 'wb');
        if ($out === false) {
            throw new \RuntimeException('Cannot write the backup file.');
        }

        try {
            fwrite($out, "-- Backup of `{$database}` taken " . date('Y-m-d H:i:s') . "\n");
            fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSET time_zone = \"+00:00\";\n\n");

            foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                $table = array_values((array) $row)[0];
                $create = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
                fwrite($out, "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

                // A page at a time: the old dump built the whole database in
                // one string and ran out of memory on real data.
                $offset = 0;
                $page = 500;
                do {
                    $rows = DB::table($table)->offset($offset)->limit($page)->get();
                    if ($rows->isEmpty()) {
                        break;
                    }
                    $columns = '`' . implode('`, `', array_keys((array) $rows->first())) . '`';
                    $values = $rows->map(function ($r) use ($pdo) {
                        return '(' . implode(', ', array_map(
                            fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                            (array) $r
                        )) . ')';
                    })->implode(",\n");
                    fwrite($out, "INSERT INTO `{$table}` ({$columns}) VALUES\n{$values};\n");
                    $offset += $page;
                } while ($rows->count() === $page);

                fwrite($out, "\n");
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($out);
        }
    }
}
