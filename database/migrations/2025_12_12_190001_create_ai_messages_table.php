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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('conversation_id')->index(); // UUID for grouping messages
            $table->enum('role', ['user', 'assistant'])->default('user');
            $table->text('content');
            $table->integer('tokens_used')->nullable();
            $table->string('provider')->nullable(); // Which AI provider was used
            $table->string('model')->nullable(); // Which model was used
            $table->timestamps();
            
            $table->index(['user_id', 'conversation_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
