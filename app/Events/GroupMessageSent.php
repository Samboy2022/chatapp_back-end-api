<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $group;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, Chat $group)
    {
        $this->message = $message;
        $this->group = $group;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->group->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'group.message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'sender_id' => $this->message->sender_id,
                'message' => $this->message->message,
                'type' => $this->message->type,
                'media_url' => $this->message->media_url,
                'media_type' => $this->message->media_type,
                'media_size' => $this->message->media_size,
                'duration' => $this->message->duration,
                'reply_to_id' => $this->message->reply_to_id,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
                'sender' => $this->message->sender ? [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                    'phone_number' => $this->message->sender->phone_number,
                    'avatar_url' => $this->message->sender->avatar_url,
                ] : null,
                'reply_to_message' => $this->message->replyToMessage ? [
                    'id' => $this->message->replyToMessage->id,
                    'message' => $this->message->replyToMessage->message,
                    'type' => $this->message->replyToMessage->type,
                    'sender' => $this->message->replyToMessage->sender ? [
                        'id' => $this->message->replyToMessage->sender->id,
                        'name' => $this->message->replyToMessage->sender->name,
                    ] : null,
                ] : null,
            ],
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'description' => $this->group->description,
                'type' => $this->group->type,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
