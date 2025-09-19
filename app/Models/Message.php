<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'reply_to_message_id',
        'message_type',
        'content',
        'media_url',
        'media_size',
        'media_duration',
        'media_mime_type',
        'file_name',
        'thumbnail_url',
        'latitude',
        'longitude',
        'location_name',
        'contact_data',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'edited_at',
        'is_deleted',
    ];

    protected $casts = [
        'contact_data' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
        'media_size' => 'integer',
        'media_duration' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships

    /**
     * Get the chat that the message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who sent the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the message this message is replying to
     */
    public function replyToMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    /**
     * Get the replies to this message
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    /**
     * Get the reactions to this message
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    // Helper methods

    /**
     * Check if message is text
     */
    public function isText(): bool
    {
        return $this->message_type === 'text';
    }

    /**
     * Check if message is media (image, video, audio, document)
     */
    public function isMedia(): bool
    {
        return in_array($this->message_type, ['image', 'video', 'audio', 'document']);
    }

    /**
     * Check if message is location
     */
    public function isLocation(): bool
    {
        return $this->message_type === 'location';
    }

    /**
     * Check if message is contact
     */
    public function isContact(): bool
    {
        return $this->message_type === 'contact';
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Get message content for display
     */
    public function getDisplayContent(): string
    {
        if ($this->is_deleted) {
            return 'This message was deleted';
        }

        switch ($this->message_type) {
            case 'text':
                return $this->content;
            case 'image':
                return '📷 Photo';
            case 'video':
                return '🎥 Video';
            case 'audio':
                return '🎵 Audio';
            case 'document':
                return '📄 ' . ($this->file_name ?? 'Document');
            case 'location':
                return '📍 Location';
            case 'contact':
                return '👤 Contact';
            default:
                return 'Message';
        }
    }

    /**
     * Soft delete the message (mark as deleted)
     */
    public function softDeleteMessage(): void
    {
        $this->update(['is_deleted' => true]);
    }

    /**
     * Get formatted media size
     */
    public function getFormattedSize(): string
    {
        if (!$this->media_size) {
            return '';
        }

        $bytes = $this->media_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get formatted duration for audio/video
     */
    public function getFormattedDuration(): string
    {
        if (!$this->media_duration) {
            return '';
        }

        $seconds = $this->media_duration;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
