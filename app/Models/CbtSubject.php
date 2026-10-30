<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** A CBT subject (English Language, Mathematics, ...). */
class CbtSubject extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function topics(): HasMany
    {
        return $this->hasMany(CbtTopic::class, 'subject_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class, 'subject_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** Find a subject by name, creating it if it's new (CSV import). */
    public static function findOrCreateByName(string $name): self
    {
        $name = trim($name);
        $slug = self::slugFor($name);

        return self::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'sort_order' => (int) self::max('sort_order') + 1,
        ]);
    }

    /** "English Language" and "English" are the same subject. */
    public static function slugFor(string $name): string
    {
        $slug = Str::slug($name);

        return match ($slug) {
            'english-language', 'use-of-english' => 'english',
            'maths', 'math', 'general-mathematics' => 'mathematics',
            default => $slug,
        };
    }
}
