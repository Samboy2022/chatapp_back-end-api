<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A topic within a CBT subject (Algebra, Comprehension, ...). */
class CbtTopic extends Model
{
    protected $fillable = ['subject_id', 'name'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(CbtSubject::class, 'subject_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class, 'topic_id');
    }
}
