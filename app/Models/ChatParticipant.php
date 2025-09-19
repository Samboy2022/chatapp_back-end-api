<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Pivot
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'chat_id',
        'user_id',
        'role',
        'joined_at',
        'left_at',
        'muted_until',
        'last_read_message_id',
        'is_archived',
        'is_pinned',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'muted_until' => 'datetime',
        'is_archived' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    // Relationships

    /**
     * Get the chat
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last read message
     */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_read_message_id');
    }

    // Helper methods

    /**
     * Check if participant is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if participant is muted
     */
    public function isMuted(): bool
    {
        return $this->muted_until && $this->muted_until->isFuture();
    }

    /**
     * Check if participant has left the chat
     */
    public function hasLeft(): bool
    {
        return !is_null($this->left_at);
    }
}
