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
        Schema::create('status_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained()->onDelete('cascade');
            $table->foreignId('viewer_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('viewed_at')->useCurrent();
            $table->string('viewer_ip')->nullable(); // Optional: track IP for analytics
            $table->string('viewer_device')->nullable(); // Optional: track device type
            $table->timestamps();

            // Prevent duplicate views from same user
            $table->unique(['status_id', 'viewer_id']);
            
            // Indexes for performance
            $table->index(['status_id', 'viewed_at']);
            $table->index(['viewer_id', 'viewed_at']);
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_views');
    }
};
