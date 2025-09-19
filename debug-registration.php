<?php
/**
 * Debug Registration Script
 * Tests the registration endpoint to see what validation errors occur
 */

echo "=== Debug Registration ===\n";

// Test data with unique values
$timestamp = time();
$testData = [
    'name' => 'Test User',
    'email' => "test_{$timestamp}@example.com",
    'phone_number' => '+123456789' . (100 + rand(1, 999)),
    'country_code' => '+1',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

echo "Testing with data:\n";
print_r($testData);

// Make request
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
$error = curl_error($ch);

curl_close($ch);

echo "\nResponse Status: $httpCode\n";
echo "Response Body:\n";
echo $response . "\n";

if ($error) {
    echo "cURL Error: $error\n";
}

// Try to decode JSON response
$responseData = json_decode($response, true);
if ($responseData && isset($responseData['errors'])) {
    echo "\nValidation Errors:\n";
    print_r($responseData['errors']);
} elseif ($responseData && $responseData['success']) {
    echo "\n✅ Registration Successful!\n";
    echo "User ID: " . $responseData['data']['user']['id'] . "\n";
    echo "Token: " . substr($responseData['data']['token'], 0, 20) . "...\n";
}
?>
