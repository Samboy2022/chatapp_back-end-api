<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_channels', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            // YouTube channel/video URL, an HLS (.m3u8) stream, or any page the
            // app can open in its webview.
            $table->string('stream_url', 2048);

            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('thumbnail_public_id')->nullable();
            $table->string('thumbnail_disk')->nullable();
            $table->string('thumbnail_path')->nullable();

            // Marks a channel as currently broadcasting, so the app can badge it.
            $table->boolean('is_live')->default(false);
            $table->boolean('is_active')->default(true);

            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_channels');
    }
};
