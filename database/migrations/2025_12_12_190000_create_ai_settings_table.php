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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('openai'); // openai, gemini, openrouter
            $table->string('model')->default('gpt-3.5-turbo');
            $table->text('api_key')->nullable(); // Encrypted in model
            $table->text('system_prompt')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('max_tokens')->default(2048);
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->timestamps();
            
            $table->index('provider');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
