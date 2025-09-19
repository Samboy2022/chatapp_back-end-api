<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to broadcast_settings table
        if (Schema::hasTable('broadcast_settings')) {
            Schema::table('broadcast_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('broadcast_settings', 'status')) {
                    $table->enum('status', ['enabled', 'disabled'])->default('enabled')->after('is_active');
                }
            });
        }

        // Create realtime_settings table for simplified configuration
        Schema::create('realtime_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->enum('driver', ['pusher', 'reverb'])->default('pusher');
            $table->string('pusher_app_id')->nullable();
            $table->string('pusher_key')->nullable();
            $table->string('pusher_secret')->nullable();
            $table->string('pusher_cluster')->default('mt1');
            $table->string('reverb_app_id')->nullable();
            $table->string('reverb_key')->nullable();
            $table->string('reverb_secret')->nullable();
            $table->string('reverb_host')->default('127.0.0.1');
            $table->integer('reverb_port')->default(8080);
            $table->string('reverb_scheme')->default('http');
            $table->timestamps();
        });

        // Insert default realtime settings
        DB::table('realtime_settings')->insert([
            'status' => 'enabled',
            'driver' => 'pusher',
            'pusher_app_id' => env('PUSHER_APP_ID', '2012149'),
            'pusher_key' => env('PUSHER_APP_KEY', 'b3652bc3e7cddc5d6f80'),
            'pusher_secret' => env('PUSHER_APP_SECRET', 'a58bf3bdccfb58ded089'),
            'pusher_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            'reverb_app_id' => env('REVERB_APP_ID', 'chatapp'),
            'reverb_key' => env('REVERB_APP_KEY', 'chatapp-key'),
            'reverb_secret' => env('REVERB_APP_SECRET', 'chatapp-secret'),
            'reverb_host' => env('REVERB_HOST', '127.0.0.1'),
            'reverb_port' => env('REVERB_PORT', 8080),
            'reverb_scheme' => env('REVERB_SCHEME', 'http'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update existing broadcast_settings if they exist
        if (Schema::hasTable('broadcast_settings')) {
            // Set all existing settings to enabled status
            DB::table('broadcast_settings')->update(['status' => 'enabled']);
            
            // Add broadcast status setting if it doesn't exist
            $existingSetting = DB::table('broadcast_settings')
                ->where('key', 'broadcast_status')
                ->first();
                
            if (!$existingSetting) {
                DB::table('broadcast_settings')->insert([
                    'key' => 'broadcast_status',
                    'value' => 'enabled',
                    'type' => 'string',
                    'group' => 'general',
                    'label' => 'Broadcast Status',
                    'description' => 'Enable or disable broadcasting system',
                    'is_required' => true,
                    'is_sensitive' => false,
                    'validation_rules' => json_encode(['required', 'in:enabled,disabled']),
                    'options' => json_encode([
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled'
                    ]),
                    'sort_order' => 1,
                    'is_active' => true,
                    'status' => 'enabled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop realtime_settings table
        Schema::dropIfExists('realtime_settings');

        // Remove status column from broadcast_settings
        if (Schema::hasTable('broadcast_settings')) {
            Schema::table('broadcast_settings', function (Blueprint $table) {
                if (Schema::hasColumn('broadcast_settings', 'status')) {
                    $table->dropColumn('status');
                }
            });

            // Remove broadcast_status setting
            DB::table('broadcast_settings')
                ->where('key', 'broadcast_status')
                ->delete();
        }
    }
};
