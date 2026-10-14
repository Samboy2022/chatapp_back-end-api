<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user notification preferences. GET /settings/notifications used to
 * return fixed defaults and PUT saved nothing; null means "all defaults".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'notification_settings')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('notification_settings')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'notification_settings')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notification_settings');
            });
        }
    }
};
