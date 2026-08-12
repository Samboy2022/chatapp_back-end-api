<?php

namespace App\Models;

use App\Helpers\ImageUrlHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A promotional slide on the app's Features screen.
 */
class Slider extends Model
{
    use HasFactory;

    /** How the image fills its box, offered to the admin as a dropdown. */
    public const FITS = [
        'cover' => 'Cover — fill the box, cropping the edges',
        'contain' => 'Contain — show the whole image, may letterbox',
        'fill' => 'Fill — stretch to the box, may distort',
    ];

    /** Used when neither the slide nor the global setting says otherwise. */
    public const DEFAULT_HEIGHT = 180;

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'image_public_id',
        'image_disk',
        'image_path',
        'link_url',
        'link_label',
        'height',
        'image_fit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Display order: the admin's explicit ordering first, then oldest-created
     * as a stable tiebreak so slides don't shuffle between requests.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    /**
     * Absolute image URL.
     *
     * Locally stored images are kept as relative paths, which the app can't
     * resolve on its own — this is the same helper the settings logo uses.
     */
    public function getResolvedImageUrlAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return ImageUrlHelper::fullUrl($this->image_url);
    }

    /** Height for this slide, falling back to the global setting. */
    public function effectiveHeight(): int
    {
        if ($this->height !== null && $this->height > 0) {
            return $this->height;
        }

        return (int) (Setting::get('slider_height') ?: self::DEFAULT_HEIGHT);
    }

    /**
     * The shape the mobile app consumes.
     *
     * Kept here rather than in the controller so the admin preview and the API
     * can't drift apart on what a slide actually is.
     */
    public function toAppArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image_url' => $this->resolved_image_url,
            'link_url' => $this->link_url,
            'link_label' => $this->link_label,
            'height' => $this->effectiveHeight(),
            'image_fit' => $this->image_fit ?: 'cover',
            'sort_order' => $this->sort_order,
        ];
    }

    /** Next free position, so a new slide lands at the end of the list. */
    public static function nextSortOrder(): int
    {
        return (int) static::max('sort_order') + 1;
    }
}
