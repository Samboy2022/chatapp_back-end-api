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
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->timestamp('cleared_at')->nullable()->after('is_pinned');
            $table->timestamp('deleted_at')->nullable()->after('cleared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->dropColumn(['cleared_at', 'deleted_at']);
        });
    }
};
