<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A candidate's answer to one question of an attempt (null = skipped). */
class CbtAttemptAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'attempt_id', 'question_id', 'subject_id', 'topic_id', 'position',
        'selected_option', 'correct_option', 'is_correct',
    ];

    protected $casts = ['is_correct' => 'boolean', 'position' => 'integer'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'question_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(CbtSubject::class, 'subject_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CbtTopic::class, 'topic_id');
    }
}
