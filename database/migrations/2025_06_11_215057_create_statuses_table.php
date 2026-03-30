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
            $table->enum('content_type', ['text', 'image', 'video']);
            $table->text('content')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('background_color', 7)->nullable();
            $table->string('font_style', 50)->nullable();
            $table->json('privacy_settings')->nullable(); // For custom privacy
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['expires_at']);
            $table->index(['content_type']);
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
