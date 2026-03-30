<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'video'])->default('text');
            $table->text('content')->nullable(); // Text content or media URL
            $table->string('media_url')->nullable(); // Full media URL for images/videos
            $table->string('media_type')->nullable(); // MIME type for media files
            $table->integer('media_size')->nullable(); // File size in bytes
            $table->integer('media_duration')->nullable(); // Duration for videos in seconds
            $table->json('media_metadata')->nullable(); // Additional metadata (dimensions, etc.)
            $table->string('background_color', 7)->nullable(); // Hex color for text statuses
            $table->string('text_color', 7)->default('#FFFFFF'); // Text color for text statuses
            $table->enum('font_style', ['normal', 'bold', 'italic'])->default('normal');
            $table->integer('font_size')->default(24); // Font size for text statuses
            $table->timestamp('expires_at')->nullable(); // Auto-calculated as created_at + 24 hours
            $table->boolean('is_active')->default(true); // Soft delete flag
            $table->integer('view_count')->default(0); // Cached view count for performance
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['expires_at', 'is_active']);
            $table->index(['created_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
