<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatPushService;
use App\Services\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class HardcodedFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $me;
    private User $ada;
    private User $bo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->me = User::factory()->create(['name' => 'Me']);
        $this->ada = User::factory()->create(['name' => 'Ada']);
        $this->bo = User::factory()->create(['name' => 'Bo']);
        Sanctum::actingAs($this->me);
    }

    private function privateChat(User $other): Chat
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->me->id, $other->id]);
        return $chat;
    }

    private function group(): Chat
    {
        $g = Chat::factory()->create(['type' => 'group', 'name' => 'Farmers', 'created_by' => $this->me->id]);
        $g->participants()->attach($this->me->id, ['role' => 'admin', 'joined_at' => now()->subDays(3)]);
        $g->participants()->attach($this->ada->id, ['role' => 'member', 'joined_at' => now()->subDays(2)]);
        $g->participants()->attach($this->bo->id, ['role' => 'member', 'joined_at' => now()->subDay()]);
        return $g;
    }

    private function say(Chat $chat, User $from, string $text, string $type = 'text'): Message
    {
        return Message::create([
            'chat_id' => $chat->id, 'sender_id' => $from->id,
            'message_type' => $type, 'content' => $text, 'status' => 'sent',
        ]);
    }

    // ── Security ───────────────────────────────────────────────────────────

    public function test_public_admin_settings_api_is_gone()
    {
        // Only the API's GET fallback is left at these paths (405 for POST).
        $this->assertContains($this->postJson('/api/admin/settings/update', ['settings' => []])->status(), [404, 405]);
        $this->getJson('/api/admin/settings/export')->assertNotFound();
    }

    // ── Notification settings ──────────────────────────────────────────────

    public function test_notification_settings_are_saved_per_user()
    {
        $this->getJson('/api/settings/notifications')->assertOk()
            ->assertJsonPath('data.group_notifications', true);

        $this->putJson('/api/settings/notifications', [
            'group_notifications' => false,
            'notification_preview' => 'name_only',
        ])->assertOk();

        $this->getJson('/api/settings/notifications')
            ->assertJsonPath('data.group_notifications', false)
            ->assertJsonPath('data.notification_preview', 'name_only')
            ->assertJsonPath('data.message_notifications', true);

        Sanctum::actingAs($this->ada);
        $this->getJson('/api/settings/notifications')->assertJsonPath('data.group_notifications', true);
    }

    private function pushSpy(): array
    {
        $calls = [];
        $push = Mockery::mock(OneSignalService::class);
        $push->shouldReceive('isConfigured')->andReturn(true);
        $push->shouldReceive('sendToUsers')->andReturnUsing(function ($ids, $title, $body) use (&$calls) {
            $calls[] = compact('ids', 'title', 'body');
            return true;
        });
        $this->app->instance(OneSignalService::class, $push);
        return [&$calls];
    }

    public function test_pushes_respect_group_setting_and_preview_choice()
    {
        $group = $this->group();
        $this->ada->forceFill(['notification_settings' => ['group_notifications' => false]])->save();
        $this->bo->forceFill(['notification_settings' => ['notification_preview' => 'name_only']])->save();
        [$calls] = [null];
        $calls = [];
        $push = Mockery::mock(OneSignalService::class);
        $push->shouldReceive('isConfigured')->andReturn(true);
        $push->shouldReceive('sendToUsers')->andReturnUsing(function ($ids, $title, $body) use (&$calls) {
            $calls[] = compact('ids', 'title', 'body');
            return true;
        });
        $this->app->instance(OneSignalService::class, $push);

        app(ChatPushService::class)->notifyNewMessage($this->say($group, $this->me, 'Prices up'));

        $this->assertCount(1, $calls); // Ada opted out of groups
        $this->assertSame([(string) $this->bo->id], $calls[0]['ids']);
        $this->assertSame('New message from Me', $calls[0]['body']); // name only
    }

    public function test_photo_push_says_photo()
    {
        $chat = $this->privateChat($this->ada);
        $calls = [];
        $push = Mockery::mock(OneSignalService::class);
        $push->shouldReceive('isConfigured')->andReturn(true);
        $push->shouldReceive('sendToUsers')->andReturnUsing(function ($ids, $title, $body) use (&$calls) {
            $calls[] = $body;
            return true;
        });
        $this->app->instance(OneSignalService::class, $push);

        app(ChatPushService::class)->notifyNewMessage($this->say($chat, $this->me, '', 'image'));

        $this->assertSame(['📷 Photo'], $calls);
    }

    // ── Devices ────────────────────────────────────────────────────────────

    public function test_devices_lists_real_sessions_and_logs_others_out()
    {
        $this->me->createToken('Android phone');
        $current = $this->me->createToken('iPhone');
        $this->app['auth']->forgetGuards();

        $headers = ['Authorization' => 'Bearer ' . $current->plainTextToken];
        $devices = $this->getJson('/api/devices', $headers)->assertOk()->json('data');

        $this->assertCount(2, $devices);
        $this->assertSame('iPhone', collect($devices)->firstWhere('is_current', true)['name']);

        $this->deleteJson('/api/devices/others', [], $headers)->assertOk()->assertJsonPath('data.logged_out', 1);
        $this->assertSame(1, $this->me->tokens()->count());
    }

    public function test_login_token_is_named_after_the_device()
    {
        $this->assertSame('Galaxy phone', \App\Support\DeviceName::from(
            \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['HTTP_X_DEVICE_NAME' => 'Galaxy phone'])
        ));
    }

    // ── Clear / delete chat ────────────────────────────────────────────────

    public function test_clear_chat_really_hides_old_messages()
    {
        $chat = $this->privateChat($this->ada);
        $this->say($chat, $this->ada, 'old');
        $this->travel(1)->seconds();
        $this->postJson("/api/chats/{$chat->id}/clear")->assertOk();
        $this->travel(1)->seconds();
        $this->say($chat, $this->ada, 'new');

        $contents = collect($this->getJson("/api/chats/{$chat->id}/messages")->json('data.data'))->pluck('content');
        $this->assertSame(['new'], $contents->all());
    }

    public function test_deleted_chat_stays_gone_until_a_new_message()
    {
        $chat = $this->privateChat($this->ada);
        $this->say($chat, $this->ada, 'old');
        $this->travel(1)->seconds();
        $this->postJson("/api/chats/{$chat->id}/delete")->assertOk();

        $ids = fn () => collect($this->getJson('/api/chats')->json('data.chats'))->pluck('id');
        $this->assertNotContains($chat->id, $ids());

        $this->travel(1)->seconds();
        $this->say($chat, $this->ada, 'are you there?');
        $this->assertContains($chat->id, $ids());
    }

    // ── Groups ─────────────────────────────────────────────────────────────

    public function test_admin_can_promote_and_dismiss_but_not_leave_zero_admins()
    {
        $group = $this->group();

        $this->putJson("/api/groups/{$group->id}/members/{$this->ada->id}/role", ['role' => 'admin'])
            ->assertOk()->assertJsonPath('message', 'Ada is now an admin');
        $this->putJson("/api/groups/{$group->id}/members/{$this->ada->id}/role", ['role' => 'member'])
            ->assertOk();
        $this->putJson("/api/groups/{$group->id}/members/{$this->me->id}/role", ['role' => 'member'])
            ->assertStatus(422);

        Sanctum::actingAs($this->bo);
        $this->putJson("/api/groups/{$group->id}/members/{$this->bo->id}/role", ['role' => 'admin'])
            ->assertStatus(403);
    }

    public function test_last_admin_leaving_hands_the_group_on()
    {
        $group = $this->group();

        $this->postJson("/api/groups/{$group->id}/leave")->assertOk();

        $this->assertSame('admin', $group->participants()->where('users.id', $this->ada->id)->first()->pivot->role);
    }

    public function test_admin_deletes_group_for_everyone()
    {
        $group = $this->group();
        $this->say($group, $this->ada, 'hi');

        Sanctum::actingAs($this->ada);
        $this->deleteJson("/api/groups/{$group->id}")->assertStatus(403);

        Sanctum::actingAs($this->me);
        $this->deleteJson("/api/groups/{$group->id}")->assertOk();
        $this->assertSoftDeleted('chats', ['id' => $group->id]);

        Sanctum::actingAs($this->ada);
        $ids = collect($this->getJson('/api/chats')->json('data.chats'))->pluck('id');
        $this->assertNotContains($group->id, $ids);
    }

    // ── Profile photo ──────────────────────────────────────────────────────

    public function test_remove_profile_photo()
    {
        $this->me->forceFill(['avatar_url' => 'https://cdn.example.com/me.jpg'])->save();

        $this->deleteJson('/api/settings/profile/avatar')->assertOk();

        $this->assertNull($this->me->fresh()->getRawOriginal('avatar_url'));
    }
}
