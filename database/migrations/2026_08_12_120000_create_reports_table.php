<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User-submitted reports (abuse, spam, harassment…), so moderators have
 * something to act on rather than blocks happening silently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();

            // Optional pointers to what exactly was reported.
            $table->foreignId('message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->foreignId('chat_id')->nullable()->constrained('chats')->nullOnDelete();

            $table->string('reason');
            $table->text('details')->nullable();

            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])
                ->default('pending');

            // Whether the reporter also blocked the person — useful context
            // when triaging, and lets the admin see block activity in one place.
            $table->boolean('blocked_by_reporter')->default(false);

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderator_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('reported_user_id');
            // One open report per person per reporter keeps the queue clean;
            // re-reporting updates the existing row instead of piling up.
            $table->index(['reporter_id', 'reported_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
