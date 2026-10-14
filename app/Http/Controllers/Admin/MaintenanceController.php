<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemMaintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Settings → Maintenance. Every action answers JSON for the page's console,
 * is written to the log with the admin who ran it, and the destructive ones
 * need the confirmation word typed back (checked here, not only in the UI).
 */
class MaintenanceController extends Controller
{
    public function __construct(private SystemMaintenance $maintenance)
    {
    }

    public function index(): View
    {
        return view('admin.settings.maintenance', [
            'overview' => $this->maintenance->overview(),
            'caches' => array_keys(SystemMaintenance::CACHES),
        ]);
    }

    public function overview(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'overview' => $this->maintenance->overview(),
            'backups' => $this->maintenance->backups()->all(),
        ]);
    }

    public function migrations(): JsonResponse
    {
        return response()->json(['success' => true] + $this->maintenance->migrationStatus());
    }

    public function pretend(): JsonResponse
    {
        return $this->run('migrate:pretend', fn () => [
            'output' => $this->maintenance->pretendMigrate(),
        ]);
    }

    /** Pending migrations, after an automatic backup. */
    public function migrate(): JsonResponse
    {
        return $this->run('migrate', function () {
            $result = $this->maintenance->migrate(withBackup: true);
            return [
                'message' => "Migrations ran. Backup taken first: {$result['backup']}",
                'output' => $result['output'],
            ];
        });
    }

    /** Pending migrations with no backup — for when the backup itself fails. */
    public function forceMigrate(Request $request): JsonResponse
    {
        if ($denied = $this->requireConfirmation($request, 'MIGRATE')) {
            return $denied;
        }
        return $this->run('migrate:force', fn () => [
            'message' => 'Migrations ran without a backup.',
            'output' => $this->maintenance->migrate(withBackup: false)['output'],
        ]);
    }

    public function rollbackPreview(Request $request): JsonResponse
    {
        $steps = (int) $request->query('steps', 1);
        return response()->json([
            'success' => true,
            'migrations' => $this->maintenance->rollbackPreview(max(1, min($steps, 20))),
        ]);
    }

    public function rollback(Request $request): JsonResponse
    {
        if ($denied = $this->requireConfirmation($request, 'ROLLBACK')) {
            return $denied;
        }
        $steps = (int) $request->input('steps', 1);

        return $this->run("migrate:rollback x{$steps}", function () use ($steps) {
            $result = $this->maintenance->rollback($steps);
            return [
                'message' => "Rolled back. Backup taken first: {$result['backup']}",
                'output' => $result['output'],
            ];
        });
    }

    public function clearCache(Request $request): JsonResponse
    {
        $which = (string) $request->input('cache', 'all');
        return $this->run("cache:clear {$which}", fn () => [
            'output' => $this->maintenance->clearCache($which),
        ]);
    }

    public function optimize(): JsonResponse
    {
        return $this->run('optimize (safe)', fn () => [
            'output' => SystemMaintenance::cacheSafely(),
        ]);
    }

    public function backup(): JsonResponse
    {
        return $this->run('backup', function () {
            $backup = $this->maintenance->backups()->create();
            return [
                'message' => "Backup created: {$backup['filename']}",
                'output' => "{$backup['filename']} · {$backup['method']} · " . number_format($backup['size']) . ' bytes',
            ];
        });
    }

    public function downloadBackup(string $filename): BinaryFileResponse|JsonResponse
    {
        $path = $this->maintenance->backups()->pathFor($filename);
        if ($path === null) {
            return response()->json(['success' => false, 'message' => 'Backup not found'], 404);
        }
        $this->audit("backup:download {$filename}");
        return response()->download($path, $filename, ['Content-Type' => 'application/sql']);
    }

    public function deleteBackup(Request $request, string $filename): JsonResponse
    {
        if ($denied = $this->requireConfirmation($request, 'DELETE')) {
            return $denied;
        }
        return $this->run("backup:delete {$filename}", function () use ($filename) {
            if (!$this->maintenance->backups()->delete($filename)) {
                throw new \RuntimeException('Backup not found');
            }
            return ['message' => "Deleted {$filename}"];
        });
    }

    public function retryFailedJobs(): JsonResponse
    {
        return $this->run('queue:retry all', fn () => [
            'output' => $this->maintenance->retryFailedJobs(),
        ]);
    }

    public function restartQueue(): JsonResponse
    {
        return $this->run('queue:restart', fn () => [
            'output' => $this->maintenance->restartQueueWorkers(),
        ]);
    }

    public function linkStorage(): JsonResponse
    {
        return $this->run('storage:link', fn () => [
            'output' => $this->maintenance->linkStorage(),
        ]);
    }

    public function down(Request $request): JsonResponse
    {
        if ($denied = $this->requireConfirmation($request, 'DOWN')) {
            return $denied;
        }
        return $this->run('down', function () {
            $secret = $this->maintenance->down();
            return [
                'message' => 'The app and API are now in maintenance mode.',
                // Visiting this path sets a cookie that lets this browser in.
                'bypass_url' => url('/' . $secret),
            ];
        });
    }

    public function up(): JsonResponse
    {
        return $this->run('up', fn () => [
            'message' => 'The app is live again.',
            'output' => $this->maintenance->up(),
        ]);
    }

    public function log(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'output' => $this->maintenance->tailLog(200),
        ]);
    }

    public function clearLog(Request $request): JsonResponse
    {
        if ($denied = $this->requireConfirmation($request, 'CLEAR')) {
            return $denied;
        }
        return $this->run('log:clear', function () {
            $this->maintenance->clearLog();
            return ['message' => 'Log cleared.'];
        });
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function run(string $action, callable $work): JsonResponse
    {
        $this->audit($action);
        try {
            $result = $work();
            return response()->json(['success' => true] + $result + ['message' => 'Done.']);
        } catch (\Throwable $e) {
            Log::error("ADMIN MAINTENANCE: {$action} failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function requireConfirmation(Request $request, string $word): ?JsonResponse
    {
        if (strtoupper(trim((string) $request->input('confirm'))) === $word) {
            return null;
        }
        return response()->json([
            'success' => false,
            'message' => "Type {$word} to confirm.",
        ], 422);
    }

    private function audit(string $action): void
    {
        $admin = session('admin_user');
        $who = is_array($admin) ? ($admin['email'] ?? $admin['name'] ?? 'admin')
            : (is_object($admin) ? ($admin->email ?? $admin->name ?? 'admin') : 'admin');
        Log::info("ADMIN MAINTENANCE: {$action} by {$who}");
    }
}
