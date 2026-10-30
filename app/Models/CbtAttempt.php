<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One sitting of a CBT exam by a user. */
class CbtAttempt extends Model
{
    protected $fillable = [
        'user_id', 'exam_type', 'mode', 'duration_minutes', 'status', 'setup',
        'started_at', 'submitted_at', 'time_spent_seconds', 'total_questions',
        'correct', 'wrong', 'skipped', 'score', 'max_score',
    ];

    protected $casts = [
        'setup' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'duration_minutes' => 'integer',
        'time_spent_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CbtAttemptAnswer::class, 'attempt_id')->orderBy('position');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function examTitle(): string
    {
        return config("cbt.exams.{$this->exam_type}.title", strtoupper($this->exam_type));
    }

    public function modeTitle(): string
    {
        return config("cbt.modes.{$this->mode}.title", ucfirst($this->mode));
    }

    public function percent(): float
    {
        return $this->max_score > 0 ? round($this->score / $this->max_score * 100, 1) : 0.0;
    }
}
