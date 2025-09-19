<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'description',
        'avatar_url',
        'created_by',
        'max_participants',
        'is_active',
    ];

    protected $casts = [
        'type' => 'string',
        'max_participants' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships

    /**
     * Get the user who created the chat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the participants of the chat
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot(['role', 'joined_at', 'left_at', 'muted_until', 'last_read_message_id', 'is_archived', 'is_pinned']);
    }

    /**
     * Get the messages in the chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message in the chat
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }

    /**
     * Get the calls in the chat
     */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    // Helper methods

    /**
     * Check if chat is private (between two users)
     */
    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    /**
     * Check if chat is a group
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    /**
     * Get the other participant in a private chat
     */
    public function getOtherParticipant(User $user)
    {
        if (!$this->isPrivate()) {
            return null;
        }

        return $this->participants()->where('users.id', '!=', $user->id)->first();
    }

    /**
     * Check if user is participant of the chat
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('users.id', $user->id)->exists();
    }

    /**
     * Add participant to chat
     */
    public function addParticipant(User $user, string $role = 'member')
    {
        if ($this->hasParticipant($user)) {
            return false;
        }

        return $this->participants()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove participant from chat
     */
    public function removeParticipant(User $user)
    {
        return $this->participants()->detach($user->id);
    }

    /**
     * Get chat for two users (create if doesn't exist)
     */
    public static function getOrCreatePrivateChat(User $user1, User $user2): Chat
    {
        // Try to find existing private chat between these users
        $chat = Chat::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('users.id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('users.id', $user2->id);
            })
            ->first();

        if (!$chat) {
            $chat = Chat::create([
                'type' => 'private',
                'created_by' => $user1->id,
            ]);

            $chat->addParticipant($user1);
            $chat->addParticipant($user2);
        }

        return $chat;
    }

    /**
     * Get unread messages count for a user
     */
    public function getUnreadCount(User $user): int
    {
        $participant = $this->participants()->where('users.id', $user->id)->first();
        
        if (!$participant) {
            return 0;
        }

        $lastReadMessageId = $participant->pivot->last_read_message_id;

        if (!$lastReadMessageId) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('id', '>', $lastReadMessageId)
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    /**
     * Mark messages as read for a user
     */
    public function markAsRead(User $user, $messageId = null)
    {
        if (!$messageId) {
            $latestMessage = $this->messages()->latest('sent_at')->first();
            $messageId = $latestMessage ? $latestMessage->id : null;
        }

        if ($messageId) {
            $this->participants()->updateExistingPivot($user->id, [
                'last_read_message_id' => $messageId,
            ]);
        }
    }
}
