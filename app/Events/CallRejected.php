<?php

namespace App\Events;

use App\Models\Call;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $call;
    public $caller;
    public $recipient;

    /**
     * Create a new event instance.
     */
    public function __construct(Call $call, User $caller, User $recipient)
    {
        $this->call = $call;
        $this->caller = $caller;
        $this->recipient = $recipient;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('call.' . $this->caller->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CallRejected';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event_type' => 'call_rejected',
            'call_id' => $this->call->call_id ?? $this->call->id,
            'caller_id' => $this->caller->id,
            'recipient_id' => $this->recipient->id,
            'caller_name' => $this->caller->name,
            'caller_avatar' => $this->caller->avatar,
            'call_type' => $this->call->call_type,
            'timestamp' => now()->toISOString(),
            'metadata' => [
                'chat_id' => $this->call->chat_id,
                'status' => $this->call->status,
                'ended_at' => $this->call->ended_at?->toISOString(),
            ],
        ];
    }
}
