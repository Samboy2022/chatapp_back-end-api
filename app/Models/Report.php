<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_REVIEWING => 'Reviewing',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_DISMISSED => 'Dismissed',
    ];

    /** Offered to the app so reasons stay consistent enough to group on. */
    public const REASONS = [
        'spam' => 'Spam or scam',
        'harassment' => 'Harassment or bullying',
        'hate_speech' => 'Hate speech',
        'violence' => 'Violence or threats',
        'nudity' => 'Nudity or sexual content',
        'misinformation' => 'False information',
        'impersonation' => 'Pretending to be someone else',
        'other' => 'Something else',
    ];

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'message_id',
        'chat_id',
        'reason',
        'details',
        'status',
        'blocked_by_reporter',
        'reviewed_by',
        'moderator_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'blocked_by_reporter' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Mirror the database defaults in the model.
     *
     * Without this a freshly `create()`d report has a null `status` in memory
     * until it is reloaded, so `isOpen()` answered false for a report that was
     * very much open.
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'blocked_by_reporter' => false,
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /** Still needs a moderator to look at it. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_REVIEWING]);
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVIEWING], true);
    }
}
