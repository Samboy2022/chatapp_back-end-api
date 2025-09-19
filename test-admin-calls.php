<?php

/**
 * Test script for admin calls endpoints
 */

$baseUrl = 'http://127.0.0.1:8000';

echo "🔧 Testing Admin Calls Endpoints\n";
echo "================================\n\n";

function makeRequest($endpoint) {
    global $baseUrl;
    
    $url = $baseUrl . $endpoint;
    echo "📞 Testing: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL Error: $error\n\n";
        return false;
    }
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ Response received:\n";
            echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
            return true;
        } else {
            echo "⚠️  Response is not valid JSON:\n";
            echo substr($response, 0, 200) . "...\n\n";
            return false;
        }
    } else {
        echo "❌ Error response:\n";
        echo substr($response, 0, 200) . "...\n\n";
        return false;
    }
}

// Test endpoints
$endpoints = [
    '/admin-api/calls/realtime-stats',
    '/admin-api/calls/active',
    '/admin-api/calls/recent-activity',
    '/admin/calls/realtime-stats',
    '/admin/calls/active',
    '/admin/calls/recent-activity',
];

$results = [];

foreach ($endpoints as $endpoint) {
    $results[$endpoint] = makeRequest($endpoint);
}

// Summary
echo "📋 SUMMARY\n";
echo "==========\n";
foreach ($results as $endpoint => $success) {
    $status = $success ? '✅ PASS' : '❌ FAIL';
    echo "$status $endpoint\n";
}

echo "\n";

// Test direct database access
echo "🗄️  Testing Direct Database Access\n";
echo "===================================\n";

try {
    // Change to the Laravel directory
    chdir('c:\laragon\www\chat-app-backend\chatapp_back-end-api');
    
    // Test database connection
    $output = shell_exec('php artisan tinker --execute="echo \'Database connection test: \' . DB::connection()->getPdo() ? \'OK\' : \'FAILED\';"');
    echo "Database: " . trim($output) . "\n";
    
    // Test calls count
    $output = shell_exec('php artisan tinker --execute="echo \'Total calls: \' . App\\Models\\Call::count();"');
    echo "Calls: " . trim($output) . "\n";
    
    // Test active calls
    $output = shell_exec('php artisan tinker --execute="echo \'Active calls: \' . App\\Models\\Call::whereIn(\'status\', [\'initiated\', \'ringing\', \'answered\'])->count();"');
    echo "Active: " . trim($output) . "\n";
    
    // Test users
    $output = shell_exec('php artisan tinker --execute="echo \'Total users: \' . App\\Models\\User::count();"');
    echo "Users: " . trim($output) . "\n";
    
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test completed!\n";
