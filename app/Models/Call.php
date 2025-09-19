<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'caller_id',
        'receiver_id',
        'type',
        'call_type',
        'status',
        'started_at',
        'answered_at',
        'ended_at',
        'duration',
        'participants',
        'call_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
        'participants' => 'array',
    ];

    // Relationships

    /**
     * Get the chat this call belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who initiated the call
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the user who received the call
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the call participants
     */
    public function participants()
    {
        return $this->hasMany(CallParticipant::class);
    }

    // Scopes

    /**
     * Scope to get only audio calls
     */
    public function scopeAudio(Builder $query): Builder
    {
        return $query->where('call_type', 'audio');
    }

    /**
     * Scope to get only video calls
     */
    public function scopeVideo(Builder $query): Builder
    {
        return $query->where('call_type', 'video');
    }

    /**
     * Scope to get missed calls
     */
    public function scopeMissed(Builder $query): Builder
    {
        return $query->where('status', 'missed');
    }

    /**
     * Scope to get answered calls
     */
    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', 'answered');
    }

    // Helper methods

    /**
     * Check if call is audio
     */
    public function isAudio(): bool
    {
        return $this->call_type === 'audio';
    }

    /**
     * Check if call is video
     */
    public function isVideo(): bool
    {
        return $this->call_type === 'video';
    }

    /**
     * Check if call was missed
     */
    public function wasMissed(): bool
    {
        return $this->status === 'missed';
    }

    /**
     * Check if call was answered
     */
    public function wasAnswered(): bool
    {
        return $this->status === 'answered';
    }

    /**
     * Start the call
     */
    public function start(): void
    {
        $this->update([
            'status' => 'answered',
            'started_at' => now(),
        ]);
    }

    /**
     * End the call
     */
    public function end(): void
    {
        if ($this->started_at) {
            $this->update([
                'status' => 'ended',
                'ended_at' => now(),
                'duration' => now()->diffInSeconds($this->started_at),
            ]);
        } else {
            $this->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
        }
    }

    /**
     * Mark call as missed
     */
    public function markAsMissed(): void
    {
        $this->update([
            'status' => 'missed',
            'ended_at' => now(),
        ]);
    }

    /**
     * Mark call as declined
     */
    public function markAsDeclined(): void
    {
        $this->update([
            'status' => 'declined',
            'ended_at' => now(),
        ]);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '00:00';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get call type icon
     */
    public function getTypeIcon(): string
    {
        return $this->isVideo() ? '📹' : '📞';
    }

    /**
     * Get call status icon
     */
    public function getStatusIcon(): string
    {
        switch ($this->status) {
            case 'missed':
                return '📵';
            case 'declined':
                return '❌';
            case 'answered':
            case 'ended':
                return $this->getTypeIcon();
            default:
                return '📱';
        }
    }

    /**
     * Get call logs for a user
     */
    public static function getLogsForUser(User $user, int $limit = 50)
    {
        $chatIds = $user->chats()->pluck('chats.id');

        return static::whereIn('chat_id', $chatIds)
            ->with(['chat.participants', 'caller'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
