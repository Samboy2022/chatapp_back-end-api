<?php

/**
 * Debug Contact Search Issue
 */

$baseUrl = 'http://127.0.0.1:8000/api';

// Helper function for API requests
function makeRequest($method, $url, $data = [], $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "📊 HTTP Status: $httpCode\n";
    if ($error) {
        echo "🔴 cURL Error: $error\n";
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        echo "✅ Request successful\n";
        return $data;
    } else {
        echo "❌ Error response (status $httpCode)\n";
        if ($response) {
            $errorData = json_decode($response, true);
            if ($errorData && isset($errorData['message'])) {
                echo "Error: " . $errorData['message'] . "\n";
            } else {
                echo "Raw response: " . substr($response, 0, 200) . "\n";
            }
        }
        return false;
    }
}

echo "🔍 Contact Search Debug Test\n";
echo "============================\n\n";

// Register a test user
$timestamp = time();
$testUser = [
    'name' => 'Search Test User ' . $timestamp,
    'email' => 'searchtest.' . $timestamp . '@farmersnetwork.com',
    'phone_number' => '+123480' . substr($timestamp, -4),
    'country_code' => '+1',
    'password' => 'testpass123',
    'password_confirmation' => 'testpass123'
];

echo "1. 📝 Registering Test User...\n";
$registerResult = makeRequest('POST', $baseUrl . '/auth/register', $testUser);

if ($registerResult && isset($registerResult['data']['token'])) {
    $token = $registerResult['data']['token'];
    echo "✅ User registered successfully\n\n";
} else {
    echo "❌ User registration failed\n";
    exit(1);
}

// Test different search queries
$searchQueries = [
    'Test',
    'User',
    'Search',
    '123',
    '+123'
];

foreach ($searchQueries as $index => $query) {
    echo ($index + 2) . ". 🔍 Testing search with query: '$query'...\n";
    
    $searchUrl = $baseUrl . '/contacts/search?query=' . urlencode($query);
    echo "📞 URL: $searchUrl\n";
    
    $searchResult = makeRequest('GET', $searchUrl, [], $token);
    
    if ($searchResult) {
        echo "✅ Search successful\n";
        if (isset($searchResult['data']['data'])) {
            echo "📊 Results: " . count($searchResult['data']['data']) . "\n";
        }
    } else {
        echo "❌ Search failed\n";
    }
    echo "\n";
}

echo "🎯 Contact Search Debug Test - COMPLETE!\n";
