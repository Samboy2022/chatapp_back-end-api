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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'key')) {
                $table->string('key')->unique()->after('id');
            }

            if (!Schema::hasColumn('settings', 'value')) {
                $table->text('value')->nullable()->after('key');
            }

            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('string')->after('value'); // string, boolean, integer, json
            }

            if (!Schema::hasColumn('settings', 'group')) {
                $table->string('group')->default('general')->after('type'); // general, chat, file, user, notification, system
            }

            if (!Schema::hasColumn('settings', 'label')) {
                $table->string('label')->nullable()->after('group');
            }

            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('label');
            }

            if (!Schema::hasColumn('settings', 'options')) {
                $table->json('options')->nullable()->after('description'); // For select fields
            }

            if (!Schema::hasColumn('settings', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('options'); // Can be accessed by frontend
            }

            // Add indexes if they don't exist
            if (!Schema::hasIndex('settings', 'settings_group_key_index')) {
                $table->index(['group', 'key']);
            }

            if (!Schema::hasIndex('settings', 'settings_is_public_index')) {
                $table->index('is_public');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'key', 'value', 'type', 'group', 'label',
                'description', 'options', 'is_public'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop indexes
            if (Schema::hasIndex('settings', 'settings_group_key_index')) {
                $table->dropIndex('settings_group_key_index');
            }

            if (Schema::hasIndex('settings', 'settings_is_public_index')) {
                $table->dropIndex('settings_is_public_index');
            }
        });
    }
};
