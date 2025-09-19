<?php
/**
 * Debug Chat List Script
 * Tests the chat list endpoint to see what's causing the 500 error
 */

echo "=== Debug Chat List ===\n";

// First register a user to get a token
$timestamp = time();
$testData = [
    'name' => 'Test User',
    'email' => "test_{$timestamp}@example.com",
    'phone_number' => '+123456789' . (100 + rand(1, 999)),
    'country_code' => '+1',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

echo "Registering user...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/auth/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    $responseData = json_decode($response, true);
    $token = $responseData['data']['token'];
    echo "✅ User registered successfully\n";
    
    // Now test the chat list endpoint
    echo "\nTesting chat list endpoint...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/chats');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Chat List Response Status: $httpCode\n";
    echo "Chat List Response Body:\n";
    echo $response . "\n";
    
    if ($httpCode === 500) {
        echo "\n❌ 500 Error occurred. Check Laravel logs for details.\n";
    } elseif ($httpCode === 200) {
        echo "\n✅ Chat list retrieved successfully!\n";
    }
    
} else {
    echo "❌ User registration failed: $httpCode\n";
    echo $response . "\n";
}
?>
