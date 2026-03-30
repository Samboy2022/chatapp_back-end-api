<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'emoji',
    ];

    // Only use created_at timestamp
    const UPDATED_AT = null;

    protected $dates = ['created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the message that was reacted to
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user who reacted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods

    /**
     * Get all reactions grouped by emoji for a message
     */
    public static function getReactionSummary($messageId): array
    {
        $reactions = static::where('message_id', $messageId)
            ->with('user:id,name')
            ->get()
            ->groupBy('emoji');

        $summary = [];
        foreach ($reactions as $emoji => $reactionGroup) {
            $summary[] = [
                'emoji' => $emoji,
                'count' => $reactionGroup->count(),
                'users' => $reactionGroup->pluck('user')->toArray(),
            ];
        }

        return $summary;
    }
}
