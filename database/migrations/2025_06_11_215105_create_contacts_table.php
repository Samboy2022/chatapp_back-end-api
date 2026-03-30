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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_user_id')->constrained('users')->onDelete('cascade');
            $table->string('contact_name');
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['user_id', 'contact_user_id']);
            $table->index(['user_id']);
            $table->index(['contact_user_id']);
            $table->index(['is_blocked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
