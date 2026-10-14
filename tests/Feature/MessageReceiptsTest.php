<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * WhatsApp-style ticks: sent → delivered when the recipient's app fetches →
 * read when they open the chat (in a group, once everyone has).
 */
class MessageReceiptsTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;
    private User $bob;
    private Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alice = User::factory()->create();
        $this->bob = User::factory()->create();
        $this->chat = Chat::factory()->create(['type' => 'private']);
        $this->chat->participants()->attach([$this->alice->id, $this->bob->id]);
    }

    private function send(User $from, Chat $chat, string $text = 'hi'): Message
    {
        Sanctum::actingAs($from);
        $response = $this->postJson("/api/chats/{$chat->id}/messages", [
            'type' => 'text',
            'content' => $text,
        ])->assertStatus(201);

        return Message::findOrFail($response->json('data.message.id'));
    }

    public function test_a_new_message_is_sent_not_delivered()
    {
        $message = $this->send($this->alice, $this->chat);

        $this->assertSame('sent', $message->fresh()->status);
    }

    public function test_recipient_fetching_chats_marks_it_delivered()
    {
        $message = $this->send($this->alice, $this->chat);

        Sanctum::actingAs($this->bob);
        $this->getJson('/api/chats')->assertOk();

        $fresh = $message->fresh();
        $this->assertSame('delivered', $fresh->status);
        $this->assertNotNull($fresh->delivered_at);
    }

    public function test_sender_fetching_does_not_deliver_their_own_message()
    {
        $message = $this->send($this->alice, $this->chat);

        $this->getJson('/api/chats')->assertOk(); // still Alice

        $this->assertSame('sent', $message->fresh()->status);
    }

    public function test_bulk_read_turns_the_ticks_blue_and_clears_unread()
    {
        $first = $this->send($this->alice, $this->chat, 'one');
        $second = $this->send($this->alice, $this->chat, 'two');

        Sanctum::actingAs($this->bob);
        $this->postJson("/api/chats/{$this->chat->id}/messages/bulk-read")
            ->assertOk()
            ->assertJsonPath('success', true);

        foreach ([$first, $second] as $message) {
            $fresh = $message->fresh();
            $this->assertSame('read', $fresh->status);
            $this->assertNotNull($fresh->read_at);
            $this->assertNotNull($fresh->delivered_at);
        }
        $this->assertSame(0, $this->chat->fresh()->getUnreadCount($this->bob));
    }

    public function test_bulk_read_never_marks_your_own_messages_read()
    {
        $mine = $this->send($this->alice, $this->chat);

        $this->postJson("/api/chats/{$this->chat->id}/messages/bulk-read")->assertOk();

        $this->assertSame('sent', $mine->fresh()->status);
    }

    public function test_single_read_on_the_chat_route_uses_the_message_id()
    {
        $message = $this->send($this->alice, $this->chat);

        Sanctum::actingAs($this->bob);
        $this->postJson("/api/chats/{$this->chat->id}/messages/{$message->id}/read")
            ->assertOk();

        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_group_message_is_read_only_when_every_member_read_it()
    {
        $carol = User::factory()->create();
        $group = Chat::factory()->create(['type' => 'group', 'name' => 'G', 'created_by' => $this->alice->id]);
        $group->participants()->attach([$this->alice->id, $this->bob->id, $carol->id]);

        $message = $this->send($this->alice, $group);

        Sanctum::actingAs($this->bob);
        $this->postJson("/api/chats/{$group->id}/messages/bulk-read")->assertOk();
        $this->assertSame('delivered', $message->fresh()->status);

        Sanctum::actingAs($carol);
        $this->postJson("/api/chats/{$group->id}/messages/bulk-read")->assertOk();
        $this->assertSame('read', $message->fresh()->status);
    }

    public function test_outsiders_cannot_mark_a_chat_read()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/chats/{$this->chat->id}/messages/bulk-read")->assertStatus(403);
    }
}
