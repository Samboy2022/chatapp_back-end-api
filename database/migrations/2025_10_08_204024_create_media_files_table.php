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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('public_id')->unique(); // Cloudinary public_id
            $table->string('url'); // Cloudinary URL
            $table->string('thumbnail_url')->nullable(); // Thumbnail URL
            $table->string('type'); // image, video, audio, document, voice
            $table->string('format')->nullable(); // png, jpg, mp4, etc.
            $table->string('resource_type')->default('auto'); // image, video, raw
            $table->bigInteger('size')->default(0); // File size in bytes
            $table->string('size_formatted')->nullable(); // Human readable size
            $table->integer('width')->nullable(); // Image/Video width
            $table->integer('height')->nullable(); // Image/Video height
            $table->integer('duration')->nullable(); // Video/Audio duration in seconds
            $table->string('folder')->nullable(); // Cloudinary folder path
            $table->foreignId('chat_id')->nullable()->constrained()->onDelete('cascade'); // Related chat
            $table->foreignId('message_id')->nullable()->constrained()->onDelete('cascade'); // Related message
            $table->string('usage_type')->nullable(); // avatar, chat_avatar, status, message, etc.
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();
            $table->softDeletes(); // Soft delete for recovery
            
            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('chat_id');
            $table->index('message_id');
            $table->index('usage_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
