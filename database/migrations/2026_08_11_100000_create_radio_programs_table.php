<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radio_programs', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            // live    → the continuous broadcast (stream URL, at most one active)
            // program → a recorded show
            // archive → an older recording / past conversation
            $table->enum('type', ['live', 'program', 'archive'])->default('program');

            // Presenter / show host — radio framing rather than "artist".
            $table->string('host')->nullable();

            // For `live` this holds the stream mount; otherwise the uploaded file.
            $table->string('audio_url', 2048)->nullable();
            $table->string('audio_public_id')->nullable(); // Cloudinary handle, for deletes
            $table->string('audio_disk')->nullable();      // 'cloudinary' | 'public'
            $table->string('audio_path')->nullable();      // relative path when stored locally

            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('thumbnail_public_id')->nullable();
            $table->string('thumbnail_disk')->nullable();
            $table->string('thumbnail_path')->nullable();

            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->boolean('is_active')->default(true);
            // A live stream can't be cached to disk, so downloads are opt-out
            // per programme rather than global.
            $table->boolean('is_downloadable')->default(true);

            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->unsignedBigInteger('play_count')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);

            $table->timestamps();

            // The app's main query is "active programmes of type X, in order".
            $table->index(['type', 'is_active', 'sort_order']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_programs');
    }
};
