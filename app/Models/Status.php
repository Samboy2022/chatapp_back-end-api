<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_type',
        'content',
        'media_url',
        'thumbnail_url',
        'background_color',
        'font_style',
        'privacy_settings',
        'expires_at',
        'type',
        'text_color',
        'font_family',
        'privacy'
    ];

    protected $casts = [
        'privacy_settings' => 'array',
        'expires_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the user who posted the status
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the views for this status
     */
    public function views(): HasMany
    {
        return $this->hasMany(StatusView::class);
    }

    // Scopes

    /**
     * Scope to get only active (non-expired) statuses
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get expired statuses
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get statuses from user's contacts
     */
    public function scopeFromContacts(Builder $query, User $user): Builder
    {
        $contactIds = $user->contacts()->pluck('contact_user_id');
        return $query->whereIn('user_id', $contactIds);
    }

    // Helper methods

    /**
     * Check if status is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if status is text type
     */
    public function isText(): bool
    {
        return $this->content_type === 'text';
    }

    /**
     * Check if status is image
     */
    public function isImage(): bool
    {
        return $this->content_type === 'image';
    }

    /**
     * Check if status is video
     */
    public function isVideo(): bool
    {
        return $this->content_type === 'video';
    }

    /**
     * Set expiration time (24 hours from now)
     */
    public function setExpiration(): void
    {
        $this->update([
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Check if user has viewed this status
     */
    public function hasBeenViewedBy(User $user): bool
    {
        return $this->views()->where('viewer_id', $user->id)->exists();
    }

    /**
     * Mark status as viewed by user
     */
    public function markAsViewedBy(User $user): void
    {
        if (!$this->hasBeenViewedBy($user)) {
            $this->views()->create([
                'viewer_id' => $user->id,
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Get view count
     */
    public function getViewCount(): int
    {
        return $this->views()->count();
    }

    /**
     * Auto-expire old statuses (for scheduled job)
     */
    public static function expireOldStatuses(): int
    {
        return static::where('expires_at', '<=', now())
            ->whereNull('deleted_at')
            ->delete();
    }

    /**
     * Get recent statuses from contacts for a user
     */
    public static function getRecentFromContacts(User $user)
    {
        $contactIds = $user->contacts()
            ->where('is_blocked', false)
            ->pluck('contact_user_id');

        return static::whereIn('user_id', $contactIds)
            ->active()
            ->with(['user:id,name,avatar_url', 'views'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');
    }
}
