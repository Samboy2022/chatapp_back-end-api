<?php

/**
 * Script to run broadcast settings migration specifically
 * Run this from the Laravel project root: php run_broadcast_migration.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 Running Broadcast Settings Migration...\n";

try {
    // Check if the table already exists
    if (Schema::hasTable('broadcast_settings')) {
        echo "✅ broadcast_settings table already exists!\n";
        exit(0);
    }

    // Create the broadcast_settings table
    Schema::create('broadcast_settings', function ($table) {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->string('type')->default('string'); // string, integer, boolean, json
        $table->string('group')->default('general'); // general, pusher, reverb, redis
        $table->string('label');
        $table->text('description')->nullable();
        $table->boolean('is_required')->default(false);
        $table->boolean('is_sensitive')->default(false); // for passwords, secrets
        $table->json('validation_rules')->nullable();
        $table->json('options')->nullable(); // for select/radio options
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    echo "✅ broadcast_settings table created successfully!\n";

    // Mark the migration as run in the migrations table
    DB::table('migrations')->insert([
        'migration' => '2024_01_20_000000_create_broadcast_settings_table',
        'batch' => DB::table('migrations')->max('batch') + 1
    ]);

    echo "✅ Migration marked as completed in migrations table!\n";

    // Now run the seeder
    echo "🌱 Running broadcast settings seeder...\n";
    
    // Include the seeder
    require_once 'database/seeders/BroadcastSettingsSeeder.php';
    
    $seeder = new \Database\Seeders\BroadcastSettingsSeeder();
    $seeder->run();
    
    echo "✅ Broadcast settings seeded successfully!\n";
    echo "🎉 Broadcast settings migration and seeding completed!\n";
    echo "\n";
    echo "You can now access the admin panel at: /admin/broadcast-settings\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
