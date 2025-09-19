<?php

// Debug script to test message endpoints
$baseUrl = 'http://localhost:8000/api';

echo "=== Debug Message Endpoints ===\n";

// Test 1: Check if the route exists
echo "1. Testing route existence...\n";
$response = makeRequest('GET', $baseUrl . '/chats/1/messages/1/react');
echo "   Response: " . $response['status'] . " - " . ($response['body'] ?? 'No body') . "\n";

// Test 2: Test with POST method
echo "2. Testing POST method...\n";
$response = makeRequest('POST', $baseUrl . '/chats/1/messages/1/react', ['emoji' => '👍']);
echo "   Response: " . $response['status'] . " - " . ($response['body'] ?? 'No body') . "\n";

// Test 3: Test with different HTTP methods
echo "3. Testing different HTTP methods...\n";
$methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
foreach ($methods as $method) {
    $response = makeRequest($method, $baseUrl . '/chats/1/messages/1/react');
    echo "   $method: " . $response['status'] . "\n";
}

function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if (!empty($headers)) {
        $defaultHeaders = array_merge($defaultHeaders, $headers);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_DELETE, true);
    }
    
    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['status' => 0, 'body' => "cURL Error: " . $error];
    }
    
    return [
        'status' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true)
    ];
}

echo "\n=== Debug Complete ===\n";
