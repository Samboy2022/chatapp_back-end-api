<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\MediaStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class GroupsMuteForwardTest extends TestCase
{
    use RefreshDatabase;

    private User $me;
    private User $ada;
    private User $bo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->me = User::factory()->create();
        $this->ada = User::factory()->create(['name' => 'Ada']);
        $this->bo = User::factory()->create(['name' => 'Bo']);
        Sanctum::actingAs($this->me);
    }

    private function privateChatWith(User $other): Chat
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->me->id, $other->id]);
        return $chat;
    }

    private function group(): Chat
    {
        $chat = Chat::factory()->create(['type' => 'group', 'name' => 'Farmers', 'created_by' => $this->me->id]);
        $chat->participants()->attach($this->me->id, ['role' => 'admin']);
        $chat->participants()->attach($this->ada->id, ['role' => 'member']);
        return $chat;
    }

    // ── Forward ────────────────────────────────────────────────────────────

    public function test_forward_keeps_the_message_and_sends_the_note_after_it()
    {
        $source = $this->privateChatWith($this->ada);
        $target = $this->privateChatWith($this->bo);
        $original = Message::create([
            'chat_id' => $source->id, 'sender_id' => $this->ada->id,
            'message_type' => 'text', 'content' => 'Fertiliser prices are up', 'status' => 'sent',
        ]);

        $response = $this->postJson('/api/messages/forward', [
            'message_id' => $original->id,
            'target_chat_id' => $target->id,
            'additional_text' => 'See this',
        ])->assertOk();

        $this->assertSame('Fertiliser prices are up', $response->json('data.message.content'));
        $this->assertTrue((bool) $response->json('data.message.is_forwarded'));
        $this->assertSame('See this', $response->json('data.note_message.content'));
        $this->assertSame(2, Message::where('chat_id', $target->id)->count());
        $this->assertSame($response->json('data.note_message.id'), $target->fresh()->last_message_id);
    }

    public function test_forward_without_a_note_sends_one_message()
    {
        $source = $this->privateChatWith($this->ada);
        $target = $this->privateChatWith($this->bo);
        $original = Message::create([
            'chat_id' => $source->id, 'sender_id' => $this->ada->id,
            'message_type' => 'text', 'content' => 'hi', 'status' => 'sent',
        ]);

        $response = $this->postJson('/api/messages/forward', [
            'message_id' => $original->id,
            'target_chat_id' => $target->id,
        ])->assertOk();

        $this->assertNull($response->json('data.note_message'));
        $this->assertSame(1, Message::where('chat_id', $target->id)->count());
    }

    // ── Mute ───────────────────────────────────────────────────────────────

    private function mutedUntil(Chat $chat): ?string
    {
        return DB::table('chat_participants')
            ->where('chat_id', $chat->id)->where('user_id', $this->me->id)
            ->value('muted_until');
    }

    public function test_mute_honours_the_chosen_duration_and_the_list_reports_it()
    {
        $chat = $this->privateChatWith($this->ada);

        $this->postJson("/api/chats/{$chat->id}/mute", ['mute' => true, 'duration_minutes' => 480])->assertOk();

        $until = \Carbon\Carbon::parse($this->mutedUntil($chat));
        $this->assertEqualsWithDelta(now()->addHours(8)->timestamp, $until->timestamp, 60);

        $listed = collect($this->getJson('/api/chats')->json('data.chats'))->firstWhere('id', $chat->id);
        $this->assertTrue($listed['is_muted']);
        $this->assertNotNull($listed['muted_until']);
    }

    public function test_mute_always_lasts_until_unmuted()
    {
        $chat = $this->privateChatWith($this->ada);
        $this->postJson("/api/chats/{$chat->id}/mute", ['mute' => true, 'always' => true])->assertOk();

        $this->assertTrue(\Carbon\Carbon::parse($this->mutedUntil($chat))->isAfter(now()->addYears(5)));
    }

    public function test_unmute_really_unmutes()
    {
        $chat = $this->privateChatWith($this->ada);
        $this->postJson("/api/chats/{$chat->id}/mute", ['mute' => true, 'always' => true])->assertOk();

        $this->postJson("/api/chats/{$chat->id}/mute", ['mute' => false])->assertOk()
            ->assertJsonPath('data.is_muted', false);

        $this->assertNull($this->mutedUntil($chat));
        $listed = collect($this->getJson('/api/chats')->json('data.chats'))->firstWhere('id', $chat->id);
        $this->assertFalse($listed['is_muted']);
    }

    // ── Groups ─────────────────────────────────────────────────────────────

    public function test_admin_can_edit_group_info()
    {
        $group = $this->group();

        $this->putJson("/api/groups/{$group->id}", ['name' => 'Farmers Kano', 'description' => 'Weekly prices'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Farmers Kano');

        $this->assertSame('Weekly prices', $group->fresh()->description);
    }

    public function test_members_cannot_edit_group_info()
    {
        $group = $this->group();
        Sanctum::actingAs($this->ada);

        $this->putJson("/api/groups/{$group->id}", ['name' => 'Mine now'])->assertStatus(403);
    }

    public function test_add_members_with_user_ids_really_adds_them()
    {
        $group = $this->group();

        $this->postJson("/api/groups/{$group->id}/members", ['user_ids' => [$this->bo->id]])
            ->assertOk()
            ->assertJsonPath('data.added_count', 1)
            ->assertJsonPath('message', 'Bo was added');

        $this->assertTrue($group->participants()->where('users.id', $this->bo->id)->exists());
    }

    public function test_adding_only_existing_members_is_reported_not_faked()
    {
        $group = $this->group();

        $this->postJson("/api/groups/{$group->id}/members", ['user_ids' => [$this->ada->id]])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_group_picture_upload_saves_it_on_the_group()
    {
        $group = $this->group();
        $storage = Mockery::mock(MediaStorageService::class);
        $storage->shouldReceive('putImage')->once()->andReturn([
            'success' => true, 'url' => 'https://cdn.example.com/g.jpg', 'public_id' => 'g',
        ]);
        $this->app->instance(MediaStorageService::class, $storage);

        $this->post('/api/media/upload/chat-avatar', [
            'chat_avatar' => UploadedFile::fake()->image('g.jpg', 400, 400),
            'chat_id' => $group->id,
        ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('data.saved', true)
            ->assertJsonPath('data.group_picture', 'https://cdn.example.com/g.jpg');

        $this->assertSame('https://cdn.example.com/g.jpg', $group->fresh()->avatar_url);

        // And it comes back when the app reloads group info.
        $this->getJson("/api/groups/{$group->id}")
            ->assertJsonPath('data.group_picture', 'https://cdn.example.com/g.jpg');
    }

    public function test_members_cannot_change_the_group_picture()
    {
        $group = $this->group();
        Sanctum::actingAs($this->ada);

        $this->post('/api/media/upload/chat-avatar', [
            'chat_avatar' => UploadedFile::fake()->image('g.jpg'),
            'chat_id' => $group->id,
        ], ['Accept' => 'application/json'])->assertStatus(403);
    }
}
