<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A banner on the app's CBT Practice screen. */
class CbtSlide extends Model
{
    protected $fillable = [
        'badge', 'title', 'subtitle', 'cta_label', 'cta_target',
        'color_from', 'color_to', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function toAppArray(): array
    {
        $target = $this->cta_target;

        return [
            'id' => $this->id,
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'cta_label' => $this->cta_label,
            // The app opens an exam's setup for an exam slug, a web page for a URL.
            'cta_exam' => $target && array_key_exists($target, config('cbt.exams')) ? $target : null,
            'cta_url' => $target && preg_match('#^https?://#', $target) ? $target : null,
            'colors' => [$this->color_from, $this->color_to],
        ];
    }
}
