<?php

namespace Tests\Feature;

use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): self
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_user' => ['email' => 'admin@example.com'],
        ]);
    }

    public function test_maintenance_and_formerly_public_tools_require_an_admin()
    {
        $this->get('/admin/settings/maintenance')->assertRedirect(route('admin.login'));
        $this->getJson('/admin/settings/maintenance/migrations')->assertStatus(401);
        // These were reachable by anyone.
        $this->getJson('/admin-api/settings/export')->assertStatus(401);
        $this->getJson('/host-fix/update-url?url=https://evil.example')->assertStatus(401);
    }

    public function test_page_renders_with_overview()
    {
        $this->asAdmin()->get('/admin/settings/maintenance')
            ->assertOk()
            ->assertSee('Database migrations')
            ->assertSee('Run pending (safe)');
    }

    public function test_migration_status_lists_everything_as_ran()
    {
        $data = $this->asAdmin()->getJson('/admin/settings/maintenance/migrations')
            ->assertOk()->json();

        $this->assertTrue($data['installed']);
        $this->assertNotEmpty($data['migrations']);
        $this->assertSame([], $data['pending']);
    }

    public function test_rollback_preview_names_the_last_migration()
    {
        $last = $this->asAdmin()->getJson('/admin/settings/maintenance/migrations/rollback-preview?steps=1')
            ->assertOk()->json('migrations');

        $this->assertCount(1, $last);
    }

    public function test_destructive_actions_need_the_typed_word()
    {
        $this->asAdmin();
        $this->postJson('/admin/settings/maintenance/migrations/rollback', ['steps' => 1])->assertStatus(422);
        $this->postJson('/admin/settings/maintenance/migrations/rollback', ['steps' => 1, 'confirm' => 'yes'])->assertStatus(422);
        $this->postJson('/admin/settings/maintenance/migrations/force')->assertStatus(422);
        $this->postJson('/admin/settings/maintenance/down')->assertStatus(422);
        $this->postJson('/admin/settings/maintenance/log/clear')->assertStatus(422);
    }

    public function test_pretend_and_safe_migrate_with_nothing_pending()
    {
        $this->asAdmin();
        $this->postJson('/admin/settings/maintenance/migrations/pretend')->assertOk()->assertJsonPath('success', true);

        $result = $this->postJson('/admin/settings/maintenance/migrations/run')->assertOk()->json();
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('backup_', $result['message']);

        $this->cleanBackups();
    }

    public function test_clear_caches()
    {
        $this->asAdmin();
        $this->postJson('/admin/settings/maintenance/cache/clear', ['cache' => 'all'])->assertOk();
        $this->postJson('/admin/settings/maintenance/cache/clear', ['cache' => 'views'])->assertOk();
        $this->postJson('/admin/settings/maintenance/cache/clear', ['cache' => 'nonsense'])->assertStatus(500);
    }

    public function test_backup_create_list_download_and_delete()
    {
        $this->asAdmin();
        $this->postJson('/admin/settings/maintenance/backups')->assertOk()->assertJsonPath('success', true);

        $backups = $this->getJson('/admin/settings/maintenance/overview')->json('backups');
        $name = $backups[0]['filename'];
        $this->assertGreaterThan(0, $backups[0]['size']);

        $path = app(DatabaseBackupService::class)->pathFor($name);
        $sql = file_get_contents($path);
        // A restorable dump: SQL from the first line, no tool warnings in it.
        $this->assertStringNotContainsString('Warning', strtok($sql, "\n"));
        $this->assertStringContainsString('CREATE TABLE', $sql);

        $this->get('/admin/settings/maintenance/backups/' . $name)->assertOk()->assertDownload($name);

        $this->deleteJson('/admin/settings/maintenance/backups/' . $name)->assertStatus(422);
        $this->deleteJson('/admin/settings/maintenance/backups/' . $name, ['confirm' => 'DELETE'])->assertOk();
        $this->assertNull(app(DatabaseBackupService::class)->pathFor($name));
    }

    public function test_backup_download_rejects_other_files()
    {
        $this->asAdmin();
        foreach (['..%2F..%2F.env', '.env', 'laravel.log', 'backup_x.sql'] as $name) {
            $this->get('/admin/settings/maintenance/backups/' . $name)->assertNotFound();
        }
    }

    private function cleanBackups(): void
    {
        foreach (app(DatabaseBackupService::class)->all() as $b) {
            if (str_contains($b['filename'], 'pre-migrate')) {
                app(DatabaseBackupService::class)->delete($b['filename']);
            }
        }
    }
}
