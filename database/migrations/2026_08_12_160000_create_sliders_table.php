<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promotional slides shown at the top of the app's Features screen.
 *
 * These were three hard-coded Unsplash URLs in the Flutter app, so changing a
 * banner meant a code change and an app-store release. Moving them here lets an
 * admin add, reorder, resize and retire slides from the dashboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();

            // The image. `image_url` is what the app reads; the disk/public_id/
            // path trio is bookkeeping so the old file can be deleted when the
            // image is replaced (same shape as radio thumbnails).
            $table->string('image_url', 2048)->nullable();
            $table->string('image_public_id')->nullable();
            $table->string('image_disk')->nullable();
            $table->string('image_path', 2048)->nullable();

            // Optional tap target. Null means the slide is decorative.
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label')->nullable();

            // Per-slide sizing, so one tall hero banner can sit alongside
            // shorter ones without forcing a single height on every slide.
            // Null falls back to the global `slider_height` setting.
            $table->unsignedSmallInteger('height')->nullable();

            // How the image fills its box: cover crops to fill, contain shows
            // the whole image and may letterbox, fill stretches.
            $table->enum('image_fit', ['cover', 'contain', 'fill'])->default('cover');

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // The app's only query: active slides in display order.
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
