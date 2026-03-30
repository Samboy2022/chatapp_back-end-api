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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('last_seen_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('profile_photo_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('about_privacy', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('status_privacy', ['everyone', 'contacts', 'close_friends'])->default('everyone');
            $table->boolean('read_receipts_enabled')->default(true);
            $table->enum('groups_privacy', ['everyone', 'contacts'])->default('everyone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_seen_privacy',
                'profile_photo_privacy',
                'about_privacy',
                'status_privacy',
                'read_receipts_enabled',
                'groups_privacy'
            ]);
        });
    }
};
