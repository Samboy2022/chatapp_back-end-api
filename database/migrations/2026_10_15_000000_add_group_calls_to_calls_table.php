<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Group calls: one call record for a group chat, with everyone in it tracked
 * in call_participants. A group call has no single receiver, so receiver_id
 * becomes nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            if (!Schema::hasColumn('calls', 'is_group')) {
                $table->boolean('is_group')->default(false)->after('call_type');
            }
            $table->unsignedBigInteger('receiver_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            if (Schema::hasColumn('calls', 'is_group')) {
                $table->dropColumn('is_group');
            }
        });
    }
};
