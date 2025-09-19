<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Models\User;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chat;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender:id,name,avatar_url', 'chat.participants']);
        $this->chat = $this->message->chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Broadcast to the chat channel
        $channels[] = new PrivateChannel('chat.' . $this->chat->id);
        
        // Also broadcast to each participant's personal channel for notifications
        foreach ($this->chat->participants as $participant) {
            if ($participant->id !== $this->message->sender_id) {
                $channels[] = new PrivateChannel('user.' . $participant->id);
            }
        }
        
        return $channels;
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
                'sender' => $this->message->sender,
                'message_type' => $this->message->message_type,
                'content' => $this->message->content,
                'media_url' => $this->message->media_url,
                'media_size' => $this->message->media_size,
                'media_duration' => $this->message->media_duration,
                'media_mime_type' => $this->message->media_mime_type,
                'file_name' => $this->message->file_name,
                'thumbnail_url' => $this->message->thumbnail_url,
                'latitude' => $this->message->latitude,
                'longitude' => $this->message->longitude,
                'location_name' => $this->message->location_name,
                'contact_data' => $this->message->contact_data,
                'reply_to_message_id' => $this->message->reply_to_message_id,
                'status' => $this->message->status,
                'sent_at' => $this->message->sent_at,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
            ],
            'chat' => [
                'id' => $this->chat->id,
                'type' => $this->chat->type,
                'name' => $this->chat->name,
                'updated_at' => $this->chat->updated_at,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
