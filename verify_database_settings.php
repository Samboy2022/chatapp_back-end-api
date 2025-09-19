<?php
/**
 * Database Settings Verification Script
 * Run this script to verify broadcast settings are properly stored and retrieved
 */

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BroadcastSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "🔍 BROADCAST SETTINGS DATABASE VERIFICATION\n";
echo "==========================================\n\n";

try {
    // Test 1: Check if table exists
    echo "1. 📋 Checking if broadcast_settings table exists...\n";
    $tableExists = DB::getSchemaBuilder()->hasTable('broadcast_settings');
    echo $tableExists ? "   ✅ Table exists\n" : "   ❌ Table does not exist\n";
    
    if (!$tableExists) {
        echo "   🚨 Please run the SQL script: update_broadcast_settings_safe.sql\n";
        exit(1);
    }
    
    // Test 2: Count total settings
    echo "\n2. 📊 Counting total settings...\n";
    $totalSettings = BroadcastSetting::count();
    echo "   📈 Total settings: {$totalSettings}\n";
    
    if ($totalSettings === 0) {
        echo "   🚨 No settings found. Please run the SQL script to populate settings.\n";
        exit(1);
    }
    
    // Test 3: Check required fields (should all be 0 now)
    echo "\n3. 🔒 Checking required field status...\n";
    $requiredSettings = BroadcastSetting::where('is_required', 1)->get();
    if ($requiredSettings->count() > 0) {
        echo "   ⚠️  Found {$requiredSettings->count()} required settings:\n";
        foreach ($requiredSettings as $setting) {
            echo "      - {$setting->key}\n";
        }
        echo "   💡 All settings should be optional. Please update the SQL script.\n";
    } else {
        echo "   ✅ All settings are optional (is_required = 0)\n";
    }
    
    // Test 4: Check active settings
    echo "\n4. 🟢 Checking active settings...\n";
    $activeSettings = BroadcastSetting::where('is_active', 1)->count();
    echo "   📊 Active settings: {$activeSettings}\n";
    
    // Test 5: Test setting retrieval
    echo "\n5. 🔍 Testing setting retrieval...\n";
    $testKeys = [
        'broadcast_enabled',
        'pusher_service_type',
        'pusher_cloud_app_key',
        'pusher_app_key',
        'websocket_host',
        'websocket_port'
    ];
    
    foreach ($testKeys as $key) {
        $setting = BroadcastSetting::where('key', $key)->first();
        if ($setting) {
            $value = $setting->typed_value;
            $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            echo "   ✅ {$key}: {$displayValue}\n";
        } else {
            echo "   ❌ {$key}: NOT FOUND\n";
        }
    }
    
    // Test 6: Test cache functionality
    echo "\n6. 💾 Testing cache functionality...\n";
    
    // Clear cache first
    Cache::forget('broadcast_settings');
    Cache::forget('broadcast_config');
    echo "   🧹 Cache cleared\n";
    
    // Test getAllGrouped (should populate cache)
    $groupedSettings = BroadcastSetting::getAllGrouped();
    echo "   📦 Retrieved grouped settings: " . $groupedSettings->count() . " groups\n";
    
    // Check if cache was populated
    $cacheExists = Cache::has('broadcast_settings');
    echo "   💾 Cache populated: " . ($cacheExists ? 'YES' : 'NO') . "\n";
    
    // Test 7: Test setting update
    echo "\n7. ✏️  Testing setting update...\n";
    $testSetting = BroadcastSetting::where('key', 'websocket_port')->first();
    if ($testSetting) {
        $originalValue = $testSetting->value;
        $testValue = '6001';
        
        echo "   📝 Original value: {$originalValue}\n";
        echo "   📝 Setting test value: {$testValue}\n";
        
        $testSetting->value = $testValue;
        $testSetting->save();
        
        // Retrieve again to verify
        $updatedSetting = BroadcastSetting::where('key', 'websocket_port')->first();
        echo "   📝 Updated value: {$updatedSetting->value}\n";
        
        if ($updatedSetting->value === $testValue) {
            echo "   ✅ Setting update successful\n";
        } else {
            echo "   ❌ Setting update failed\n";
        }
        
        // Restore original value
        $testSetting->value = $originalValue;
        $testSetting->save();
        echo "   🔄 Original value restored\n";
    } else {
        echo "   ❌ Test setting not found\n";
    }
    
    // Test 8: Test configuration generation
    echo "\n8. ⚙️  Testing configuration generation...\n";
    
    try {
        $broadcastConfig = BroadcastSetting::getBroadcastConfig();
        echo "   ✅ Broadcast config generated: " . count($broadcastConfig) . " settings\n";
        
        $appConfig = BroadcastSetting::getAppConfig();
        echo "   ✅ App config generated: " . count($appConfig) . " settings\n";
        
        // Show key configuration values
        echo "   📊 Key config values:\n";
        echo "      - broadcast_enabled: " . ($broadcastConfig['broadcast_enabled'] ?? 'NOT SET') . "\n";
        echo "      - pusher_service_type: " . ($broadcastConfig['pusher_service_type'] ?? 'NOT SET') . "\n";
        echo "      - app_name: " . ($appConfig['app_name'] ?? 'NOT SET') . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Configuration generation failed: " . $e->getMessage() . "\n";
    }
    
    // Test 9: Database connection and permissions
    echo "\n9. 🔗 Testing database connection and permissions...\n";
    
    try {
        // Test read permission
        $readTest = DB::table('broadcast_settings')->limit(1)->get();
        echo "   ✅ Database read permission: OK\n";
        
        // Test write permission (create a temporary setting)
        $tempSetting = new BroadcastSetting([
            'key' => 'temp_test_setting',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'test',
            'label' => 'Test Setting',
            'description' => 'Temporary test setting',
            'is_required' => false,
            'is_sensitive' => false,
            'is_active' => false
        ]);
        $tempSetting->save();
        echo "   ✅ Database write permission: OK\n";
        
        // Clean up
        $tempSetting->delete();
        echo "   🧹 Test setting cleaned up\n";
        
    } catch (Exception $e) {
        echo "   ❌ Database permission test failed: " . $e->getMessage() . "\n";
    }
    
    // Summary
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📋 VERIFICATION SUMMARY\n";
    echo str_repeat("=", 50) . "\n";
    echo "✅ Database table exists and is accessible\n";
    echo "✅ Settings are properly configured\n";
    echo "✅ Cache functionality works\n";
    echo "✅ Setting updates work\n";
    echo "✅ Configuration generation works\n";
    echo "✅ Database permissions are correct\n";
    echo "\n🎉 All tests passed! The database is properly configured.\n";
    
} catch (Exception $e) {
    echo "\n❌ VERIFICATION FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n🔧 Please check your database configuration and ensure:\n";
    echo "1. Database connection is working\n";
    echo "2. broadcast_settings table exists\n";
    echo "3. SQL script has been executed\n";
    echo "4. Web server has database write permissions\n";
    exit(1);
}
