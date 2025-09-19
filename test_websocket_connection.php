<?php

echo "🔌 Testing WebSocket Connection\n";
echo "==============================\n\n";

// Test WebSocket server connection
$host = '192.168.0.2';
$port = 6001;

echo "Testing connection to {$host}:{$port}...\n";

$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if ($socket) {
    echo "✅ WebSocket server is running on {$host}:{$port}\n";
    fclose($socket);
} else {
    echo "❌ WebSocket server is not accessible: {$errstr} (Error: {$errno})\n";
    echo "💡 Make sure Laravel Reverb is running with: php artisan reverb:start --host=0.0.0.0 --port=6001\n";
}

echo "\n";

// Test Laravel API server
$apiHost = '192.168.0.2';
$apiPort = 8000;

echo "Testing Laravel API server at {$apiHost}:{$apiPort}...\n";

$apiSocket = @fsockopen($apiHost, $apiPort, $errno, $errstr, 5);

if ($apiSocket) {
    echo "✅ Laravel API server is running on {$apiHost}:{$apiPort}\n";
    fclose($apiSocket);
} else {
    echo "❌ Laravel API server is not accessible: {$errstr} (Error: {$errno})\n";
}

echo "\n";

// Test API endpoint
echo "Testing API endpoint...\n";

$apiUrl = "http://{$apiHost}:{$apiPort}/api/app-config";
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);

if ($response !== false) {
    echo "✅ API endpoint is accessible\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ API returns valid configuration\n";
        echo "📡 Broadcast enabled: " . ($data['data']['broadcast_enabled'] ? 'Yes' : 'No') . "\n";
        echo "🔧 Service type: " . ($data['data']['broadcast_service_type'] ?? 'Not set') . "\n";
    } else {
        echo "⚠️ API response format unexpected\n";
    }
} else {
    echo "❌ API endpoint is not accessible\n";
}

echo "\n🏁 Connection tests completed!\n";

?>
