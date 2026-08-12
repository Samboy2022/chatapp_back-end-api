<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioProgram extends Model
{
    use HasFactory;

    public const TYPE_LIVE = 'live';
    public const TYPE_PROGRAM = 'program';
    public const TYPE_ARCHIVE = 'archive';

    public const TYPES = [
        self::TYPE_LIVE => 'Live Broadcast',
        self::TYPE_PROGRAM => 'Programme',
        self::TYPE_ARCHIVE => 'Archive / Past Conversation',
    ];

    protected $fillable = [
        'title',
        'description',
        'type',
        'host',
        'audio_url',
        'audio_public_id',
        'audio_disk',
        'audio_path',
        'thumbnail_url',
        'thumbnail_public_id',
        'thumbnail_disk',
        'thumbnail_path',
        'duration_seconds',
        'file_size',
        'is_active',
        'is_downloadable',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_downloadable' => 'boolean',
        'duration_seconds' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'play_count' => 'integer',
        'download_count' => 'integer',
        'published_at' => 'datetime',
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Published now or in the past. Unset `published_at` counts as published. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** The order the mobile app renders them in. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isLive(): bool
    {
        return $this->type === self::TYPE_LIVE;
    }

    /**
     * A live stream is never cached to disk — it has no end, and a stale copy
     * of "now" is meaningless. Everything else respects the admin's flag.
     */
    public function isDownloadable(): bool
    {
        return !$this->isLive() && $this->is_downloadable && filled($this->audio_url);
    }

    /**
     * The currently on-air broadcast, if the admin has set one.
     */
    public static function currentLive(): ?self
    {
        return static::query()->active()->published()->ofType(self::TYPE_LIVE)->ordered()->first();
    }

    /**
     * Promote this programme to *the* live station.
     *
     * Only one station can be on air, so this demotes any other live entry to
     * an archive rather than leaving two competing "live" rows behind.
     */
    public function makeSoleLive(): void
    {
        static::query()
            ->where('type', self::TYPE_LIVE)
            ->where('id', '!=', $this->id)
            ->update(['type' => self::TYPE_ARCHIVE]);
    }

    public function incrementPlayCount(): void
    {
        $this->incrementQuietly('play_count');
    }

    public function incrementDownloadCount(): void
    {
        $this->incrementQuietly('download_count');
    }
}
