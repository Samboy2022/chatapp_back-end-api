<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Chat;

// Broadcast immediately, not via the queue: with QUEUE_CONNECTION=database and
// no worker running, queued broadcasts never left the jobs table.
class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $chat;
    public $isTyping;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Chat $chat, bool $isTyping = true)
    {
        $this->user = $user;
        $this->chat = $chat;
        $this->isTyping = $isTyping;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // The conversation's private channel, which the app subscribes to.
            // This was PresenceChannel('presence-chat.N'), which Laravel
            // publishes as "presence-presence-chat.N" — nobody listened there.
            new PrivateChannel('chat.' . $this->chat->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user->avatar_url,
            ],
            // Flat fields are what the app reads.
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'chat_id' => $this->chat->id,
            'is_typing' => $this->isTyping,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }
}
