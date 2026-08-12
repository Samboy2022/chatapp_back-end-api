<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TvChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'stream_url',
        'thumbnail_url',
        'thumbnail_public_id',
        'thumbnail_disk',
        'thumbnail_path',
        'is_live',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        // Live channels float to the top regardless of manual ordering.
        return $query->orderByDesc('is_live')->orderBy('sort_order')->orderByDesc('id');
    }

    public function incrementViewCount(): void
    {
        $this->incrementQuietly('view_count');
    }
}
