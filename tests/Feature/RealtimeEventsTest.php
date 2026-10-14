<?php

namespace Tests\Feature;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The events the app listens for go where it listens, in the shape it reads. */
class RealtimeEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_typing_goes_to_the_chat_private_channel_with_flat_fields()
    {
        $user = User::factory()->create(['name' => 'Ada Obi']);
        $chat = Chat::factory()->create(['type' => 'private']);

        $event = new UserTyping($user, $chat, true);

        $this->assertSame('private-chat.' . $chat->id, $event->broadcastOn()[0]->name);
        $payload = $event->broadcastWith();
        $this->assertSame($user->id, $payload['user_id']);
        $this->assertSame('Ada Obi', $payload['user_name']);
        $this->assertTrue($payload['is_typing']);
    }

    public function test_message_events_broadcast_without_a_queue_worker()
    {
        foreach ([MessageSent::class, MessageRead::class, UserTyping::class] as $class) {
            $this->assertTrue(is_subclass_of($class, ShouldBroadcastNow::class), $class);
        }
    }

    public function test_typing_endpoint_broadcasts()
    {
        Event::fake([UserTyping::class]);
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach($user->id);

        Sanctum::actingAs($user);
        $this->postJson("/api/websocket/chats/{$chat->id}/typing", ['is_typing' => true])->assertOk();

        Event::assertDispatched(UserTyping::class);
    }
}
