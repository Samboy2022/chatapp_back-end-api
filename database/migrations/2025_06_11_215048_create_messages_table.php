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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->enum('message_type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact']);
            $table->text('content')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->bigInteger('media_size')->nullable();
            $table->integer('media_duration')->nullable(); // seconds for audio/video
            $table->string('media_mime_type', 100)->nullable();
            $table->string('file_name')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->json('contact_data')->nullable();
            $table->enum('status', ['sending', 'sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['chat_id']);
            $table->index(['sender_id']);
            $table->index(['sent_at']);
            $table->index(['status']);
            $table->index(['message_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
