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
        Schema::table('calls', function (Blueprint $table) {
            // Add receiver_id column if it doesn't exist
            if (!Schema::hasColumn('calls', 'receiver_id')) {
                $table->foreignId('receiver_id')->nullable()->after('caller_id')->constrained('users')->onDelete('cascade');
            }

            // Add answered_at column if it doesn't exist
            if (!Schema::hasColumn('calls', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->after('started_at');
            }

            // Add type column if it doesn't exist
            if (!Schema::hasColumn('calls', 'type')) {
                $table->string('type')->nullable()->after('call_type'); // Add type field for compatibility
            }

            // Add indexes for better performance
            $table->index(['receiver_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->dropColumn(['receiver_id', 'answered_at', 'type']);
            $table->dropIndex(['receiver_id', 'created_at']);
        });
    }
};
