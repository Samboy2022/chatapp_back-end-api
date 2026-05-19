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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('forwarded_from_id')->nullable()->after('reply_to_message_id')->constrained('messages')->onDelete('set null');
            $table->boolean('is_forwarded')->default(false)->after('forwarded_from_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['forwarded_from_id']);
            $table->dropColumn(['forwarded_from_id', 'is_forwarded']);
        });
    }
};
