<?php

/**
 * Debug authentication issues
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "🔧 Testing Authentication Debug\n";
echo "===============================\n\n";

// Generate unique phone number for testing
$uniquePhone = '+1234567' . rand(100, 999);

function makeRequest($method, $url, $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Status: $httpCode\n";
    echo "Response: $response\n";
    
    if ($error) {
        echo "cURL Error: $error\n";
        return false;
    }
    
    return json_decode($response, true);
}

echo "1. Testing Registration with unique phone: $uniquePhone\n";
echo "URL: $baseUrl/auth/register\n";

$registerData = [
    'name' => 'Test User ' . rand(100, 999),
    'email' => 'testuser' . rand(100, 999) . '@example.com',
    'phone_number' => $uniquePhone,
    'country_code' => '+1',
    'password' => 'testpassword123',
    'password_confirmation' => 'testpassword123'
];

echo "Registration data:\n";
print_r($registerData);
echo "\n";

$registerResult = makeRequest('POST', $baseUrl . '/auth/register', $registerData);

if ($registerResult && isset($registerResult['success']) && $registerResult['success']) {
    echo "✅ Registration successful!\n";
    $token = $registerResult['data']['token'];
    echo "Token: $token\n";
    
    echo "\n2. Testing Login with same credentials\n";
    echo "URL: $baseUrl/auth/login\n";
    
    $loginData = [
        'login' => $uniquePhone,
        'password' => 'testpassword123'
    ];
    
    echo "Login data:\n";
    print_r($loginData);
    echo "\n";
    
    $loginResult = makeRequest('POST', $baseUrl . '/auth/login', $loginData);
    
    if ($loginResult && isset($loginResult['success']) && $loginResult['success']) {
        echo "✅ Login successful!\n";
        echo "Token: " . $loginResult['data']['token'] . "\n";
    } else {
        echo "❌ Login failed\n";
    }
    
} else {
    echo "❌ Registration failed\n";
    if ($registerResult && isset($registerResult['errors'])) {
        echo "Validation errors:\n";
        print_r($registerResult['errors']);
    }
}

echo "\n🎯 Authentication debug completed!\n";
