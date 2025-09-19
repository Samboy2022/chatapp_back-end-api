<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BroadcastSetting;

echo "🔧 Updating Broadcasting Configuration to Pusher Cloud...\n\n";

try {
    // 1. Set broadcast_driver to "pusher" (enable broadcasting)
    echo "1. Setting broadcast_driver to 'pusher'...\n";
    BroadcastSetting::where('key', 'broadcast_driver')->update(['value' => 'pusher']);
    echo "   ✅ broadcast_driver updated\n\n";

    // 2. Set pusher_service_type to "pusher_cloud"
    echo "2. Setting pusher_service_type to 'pusher_cloud'...\n";
    BroadcastSetting::where('key', 'pusher_service_type')->update(['value' => 'pusher_cloud']);
    echo "   ✅ pusher_service_type updated\n\n";

    // 3. Ensure broadcast_enabled is "true"
    echo "3. Setting broadcast_enabled to 'true'...\n";
    BroadcastSetting::where('key', 'broadcast_enabled')->update(['value' => 'true']);
    echo "   ✅ broadcast_enabled updated\n\n";

    // 4. Configure Pusher Cloud credentials
    echo "4. Configuring Pusher Cloud credentials...\n";
    
    // App ID: 2012149
    BroadcastSetting::where('key', 'pusher_cloud_app_id')->update(['value' => '2012149']);
    echo "   ✅ pusher_cloud_app_id: 2012149\n";
    
    // App Key: b3652bc3e7cddc5d6f80
    BroadcastSetting::where('key', 'pusher_cloud_app_key')->update(['value' => 'b3652bc3e7cddc5d6f80']);
    echo "   ✅ pusher_cloud_app_key: b3652bc3e7cddc5d6f80\n";
    
    // App Secret: a58bf3bdccfb58ded089
    BroadcastSetting::where('key', 'pusher_cloud_app_secret')->update(['value' => 'a58bf3bdccfb58ded089']);
    echo "   ✅ pusher_cloud_app_secret: a58bf3bdccfb58ded089\n";
    
    // Cluster: mt1
    BroadcastSetting::where('key', 'pusher_cloud_cluster')->update(['value' => 'mt1']);
    echo "   ✅ pusher_cloud_cluster: mt1\n";
    
    // Use TLS: true
    BroadcastSetting::where('key', 'pusher_cloud_use_tls')->update(['value' => 'true']);
    echo "   ✅ pusher_cloud_use_tls: true\n\n";

    echo "🎉 Broadcasting configuration updated successfully!\n\n";
    
    // 5. Display current configuration
    echo "📋 Current Broadcasting Configuration:\n";
    echo "=====================================\n";
    
    $settings = BroadcastSetting::whereIn('key', [
        'broadcast_driver',
        'broadcast_enabled', 
        'pusher_service_type',
        'pusher_cloud_app_id',
        'pusher_cloud_app_key',
        'pusher_cloud_app_secret',
        'pusher_cloud_cluster',
        'pusher_cloud_use_tls'
    ])->get();
    
    foreach ($settings as $setting) {
        $value = $setting->is_sensitive ? '***HIDDEN***' : $setting->value;
        echo sprintf("%-25s: %s\n", $setting->key, $value);
    }
    
    echo "\n✅ Configuration update completed!\n";
    echo "🔄 Please clear Laravel config cache and test the API endpoint.\n";

} catch (Exception $e) {
    echo "❌ Error updating configuration: " . $e->getMessage() . "\n";
    exit(1);
}
