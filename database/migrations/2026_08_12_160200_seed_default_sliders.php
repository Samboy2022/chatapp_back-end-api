<?php

use App\Models\Slider;
use Illuminate\Database\Migrations\Migration;

/**
 * Carry over the three slides that were hard-coded in the Flutter app.
 *
 * Without this, upgrading would leave the Features screen with an empty
 * carousel until an admin happened to add something. The admin can now edit or
 * delete these like any other slide.
 */
return new class extends Migration
{
    private array $rows = [
        [
            'Connect with Farmers',
            'Chat and share experiences with fellow farmers',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800',
        ],
        [
            'APC Institute of Progressives Studies',
            'Courses and study material from the Institute',
            'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800',
        ],
        [
            'Skills Acquisitions',
            'Practical training to build your trade',
            'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800',
        ],
    ];

    public function up(): void
    {
        // Only seed an empty table — re-running this must not duplicate slides
        // or resurrect ones an admin deliberately deleted.
        if (Slider::query()->exists()) {
            return;
        }

        foreach ($this->rows as $index => [$title, $subtitle, $imageUrl]) {
            Slider::create([
                'title' => $title,
                'subtitle' => $subtitle,
                'image_url' => $imageUrl,
                'image_fit' => 'cover',
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        Slider::whereIn('title', array_column($this->rows, 0))->delete();
    }
};
