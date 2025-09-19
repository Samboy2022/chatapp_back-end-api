<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'media_url',
        'media_type',
        'media_size',
        'media_duration',
        'media_metadata',
        'background_color',
        'text_color',
        'font_style',
        'font_size',
        'expires_at',
        'is_active',
        'view_count'
    ];

    protected $casts = [
        'media_metadata' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'is_expired',
        'time_remaining',
        'full_media_url'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set expires_at when creating
        static::creating(function ($status) {
            if (!$status->expires_at) {
                $status->expires_at = Carbon::now()->addHours(24);
            }
        });

        // Clean up media files when deleting
        static::deleting(function ($status) {
            if ($status->media_url && Storage::exists($status->media_url)) {
                Storage::delete($status->media_url);
            }
        });
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StatusView::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithViewCount($query)
    {
        return $query->withCount('views');
    }

    /**
     * Accessors
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTimeRemainingAttribute(): ?int
    {
        if (!$this->expires_at || $this->is_expired) {
            return 0;
        }
        
        return $this->expires_at->diffInSeconds(Carbon::now());
    }

    public function getFullMediaUrlAttribute(): ?string
    {
        if (!$this->media_url) {
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($this->media_url, 'http')) {
            return $this->media_url;
        }

        // Generate full URL for local storage
        return Storage::url($this->media_url);
    }

    /**
     * Helper methods
     */
    public function markAsViewed(User $viewer): StatusView
    {
        $view = $this->views()->firstOrCreate([
            'viewer_id' => $viewer->id,
        ], [
            'viewed_at' => Carbon::now(),
            'viewer_ip' => request()->ip(),
            'viewer_device' => request()->userAgent(),
        ]);

        // Update cached view count
        if ($view->wasRecentlyCreated) {
            $this->increment('view_count');
        }

        return $view;
    }

    public function hasBeenViewedBy(User $viewer): bool
    {
        return $this->views()->where('viewer_id', $viewer->id)->exists();
    }

    public function getViewers()
    {
        return $this->views()
            ->with('viewer:id,name,avatar_url')
            ->orderBy('viewed_at', 'desc')
            ->get()
            ->map(function ($view) {
                return [
                    'id' => $view->viewer->id,
                    'name' => $view->viewer->name,
                    'avatar' => $view->viewer->avatar_url,
                    'viewed_at' => $view->viewed_at->toISOString(),
                ];
            });
    }

    /**
     * Check if status can be viewed by user
     */
    public function canBeViewedBy(User $user): bool
    {
        // Owner can always view their own status
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if status is active and not expired
        return $this->is_active && !$this->is_expired;
    }
}
