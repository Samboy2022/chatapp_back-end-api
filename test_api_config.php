<?php

echo "🧪 Testing API Configuration Endpoint...\n\n";

$url = 'http://192.168.0.6:8000/api/app-config';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "❌ HTTP Error: $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

// Parse JSON response
$data = json_decode($response, true);

if (!$data) {
    echo "❌ Failed to parse JSON response\n";
    echo "Raw response: $response\n";
    exit(1);
}

echo "✅ API Response received successfully!\n\n";
echo "📋 Broadcasting Configuration from API:\n";
echo "======================================\n";

if (isset($data['data'])) {
    $config = $data['data'];
    
    echo sprintf("%-25s: %s\n", 'broadcast_enabled', $config['broadcast_enabled'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'broadcast_type', $config['broadcast_type'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'pusher_service_type', $config['pusher_service_type'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'pusher_cloud_app_id', $config['pusher_cloud_app_id'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'pusher_cloud_app_key', $config['pusher_cloud_app_key'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'pusher_cloud_cluster', $config['pusher_cloud_cluster'] ?? 'NOT SET');
    echo sprintf("%-25s: %s\n", 'pusher_cloud_use_tls', $config['pusher_cloud_use_tls'] ?? 'NOT SET');
    
    echo "\n🔍 Analysis:\n";
    echo "============\n";
    
    if ($config['broadcast_enabled'] === true || $config['broadcast_enabled'] === 'true') {
        echo "✅ Broadcasting is ENABLED\n";
    } else {
        echo "❌ Broadcasting is DISABLED\n";
    }
    
    if ($config['pusher_service_type'] === 'pusher_cloud') {
        echo "✅ Using Pusher Cloud API\n";
    } else {
        echo "❌ NOT using Pusher Cloud API (current: " . ($config['pusher_service_type'] ?? 'NOT SET') . ")\n";
    }
    
    if (!empty($config['pusher_cloud_app_id']) && !empty($config['pusher_cloud_app_key'])) {
        echo "✅ Pusher Cloud credentials are configured\n";
    } else {
        echo "❌ Pusher Cloud credentials are missing\n";
    }
    
} else {
    echo "❌ No 'data' field in API response\n";
    echo "Full response:\n";
    print_r($data);
}

echo "\n🎯 Expected Mobile App Behavior:\n";
echo "================================\n";
if (isset($config) && 
    ($config['broadcast_enabled'] === true || $config['broadcast_enabled'] === 'true') && 
    $config['pusher_service_type'] === 'pusher_cloud' &&
    !empty($config['pusher_cloud_app_id'])) {
    echo "✅ Mobile app should show: 'Real-time features enabled'\n";
    echo "✅ WebSocket connection should work with Pusher Cloud\n";
} else {
    echo "❌ Mobile app will still show: 'All real-time features disabled'\n";
    echo "❌ Configuration needs to be fixed\n";
}

echo "\n";
