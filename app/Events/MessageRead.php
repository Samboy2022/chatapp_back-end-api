<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Models\User;

// Broadcast immediately, not via the queue: with QUEUE_CONNECTION=database and
// no worker running, queued broadcasts never left the jobs table.
class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $reader;
    public $chat;

    /** Every message this receipt covers; [$message] alone when empty. */
    public $messageIds;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, User $reader, array $messageIds = [])
    {
        $this->message = $message;
        $this->reader = $reader;
        $this->chat = $message->chat;
        $this->messageIds = $messageIds ?: [$message->id];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'message_ids' => array_values($this->messageIds),
            'chat_id' => $this->chat->id,
            'reader' => [
                'id' => $this->reader->id,
                'name' => $this->reader->name,
                'avatar_url' => $this->reader->avatar_url,
            ],
            'read_at' => now()->toISOString(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.read';
    }
}
