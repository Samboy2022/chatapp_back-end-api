<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One multiple-choice question. `options` maps letters to text
 * ({"A": "...", "B": "..."}); `correct_option` is one of those letters.
 */
class CbtQuestion extends Model
{
    /** Exam tags a question can carry; null means "any exam". */
    public const EXAM_TYPES = ['jamb', 'waec', 'neco'];

    protected $fillable = [
        'subject_id', 'topic_id', 'exam_type', 'year', 'question',
        'options', 'correct_option', 'explanation', 'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(CbtSubject::class, 'subject_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CbtTopic::class, 'topic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Questions an exam may draw on: those tagged for it plus untagged ones,
     * or every question for an exam whose pool is "all" (Mock CBT).
     */
    public function scopeForExam(Builder $query, string $exam): Builder
    {
        if (config("cbt.exams.$exam.question_pool") === 'all') {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('exam_type', $exam)->orWhereNull('exam_type'));
    }

    /** Options as an ordered list for the app: [{key: "A", text: "..."}]. */
    public function optionList(): array
    {
        $options = $this->options ?? [];
        ksort($options);

        return collect($options)
            ->map(fn ($text, $key) => ['key' => (string) $key, 'text' => (string) $text])
            ->values()
            ->all();
    }
}
