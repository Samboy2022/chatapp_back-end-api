<?php
/**
 * Test Registration and Login API endpoints
 * Tests the live API at https://farmersnetwork.ng/
 */

echo "=== Live API Registration & Login Test ===\n";
echo "Target URL: https://farmersnetwork.ng/\n\n";

// Test data for registration
$timestamp = time();
$testData = [
    'name' => 'Test User ' . $timestamp,
    'email' => "test_user_{$timestamp}@example.com",
    'phone_number' => '+123456789' . rand(100, 999),
    'country_code' => '+1',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

echo "1. Testing Registration Endpoint (POST /api/auth/register)\n";
echo "Registration data:\n";
foreach ($testData as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

// Make registration request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://farmersnetwork.ng/api/auth/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: API-Test-Script'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$registrationResponse = curl_exec($ch);
$registrationHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$registrationError = curl_error($ch);

curl_close($ch);

echo "Registration Response Status: $registrationHttpCode\n";
if ($registrationError) {
    echo "cURL Error: $registrationError\n";
}

echo "Registration Response Body:\n";
echo $registrationResponse . "\n\n";

// Try to decode JSON response
$regResponseData = json_decode($registrationResponse, true);
$token = null;
$userId = null;

if ($regResponseData) {
    if (isset($regResponseData['success']) && $regResponseData['success']) {
        echo "✅ Registration: SUCCESS\n";
        if (isset($regResponseData['data']['token'])) {
            $token = $regResponseData['data']['token'];
            $userId = $regResponseData['data']['user']['id'] ?? null;
            echo "✅ Token received: " . substr($token, 0, 20) . "...\n";
        }
    } else {
        echo "❌ Registration: FAILED\n";
        if (isset($regResponseData['errors'])) {
            echo "Validation Errors:\n";
            print_r($regResponseData['errors']);
        }
    }
} else {
    echo "❌ Could not parse JSON response\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

echo "2. Testing Login Endpoint (POST /api/auth/login)\n";

// If registration failed, try to login with existing credentials
if (!isset($regResponseData['success']) || !$regResponseData['success']) {
    echo "Using test credentials (assuming user exists)...\n";
    $loginData = [
        'login' => 'test@example.com', // fallback test credentials
        'password' => 'password123'
    ];
} else {
    // Use the credentials we just registered with
    $loginData = [
        'login' => $testData['email'],
        'password' => $testData['password']
    ];
}

echo "Login data:\n";
foreach ($loginData as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

// Make login request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://farmersnetwork.ng/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: API-Test-Script'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loginError = curl_error($ch);

curl_close($ch);

echo "Login Response Status: $loginHttpCode\n";
if ($loginError) {
    echo "cURL Error: $loginError\n";
}

echo "Login Response Body:\n";
echo $loginResponse . "\n\n";

// Parse login response
$loginResponseData = json_decode($loginResponse, true);

if ($loginResponseData) {
    if (isset($loginResponseData['success']) && $loginResponseData['success']) {
        echo "✅ Login: SUCCESS\n";
        if (isset($loginResponseData['data']['token'])) {
            $loginToken = $loginResponseData['data']['token'];
            echo "✅ Login Token received: " . substr($loginToken, 0, 20) . "...\n";
        }
    } else {
        echo "❌ Login: FAILED\n";
        if (isset($loginResponseData['message'])) {
            echo "Error Message: " . $loginResponseData['message'] . "\n";
        }
        if (isset($loginResponseData['errors'])) {
            echo "Validation Errors:\n";
            print_r($loginResponseData['errors']);
        }
    }
} else {
    echo "❌ Could not parse JSON response\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

echo "3. Testing Protected Route (GET /api/auth/user)\n";

// Test with the login token
if (isset($loginResponseData['success']) && $loginResponseData['success'] && isset($loginResponseData['data']['token'])) {
    $authToken = $loginResponseData['data']['token'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://farmersnetwork.ng/api/auth/user');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $authToken,
        'Accept: application/json',
        'User-Agent: API-Test-Script'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $userResponse = curl_exec($ch);
    $userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $userError = curl_error($ch);

    curl_close($ch);

    echo "User Data Response Status: $userHttpCode\n";
    if ($userError) {
        echo "cURL Error: $userError\n";
    }

    echo "User Data Response Body:\n";
    echo $userResponse . "\n\n";

    $userResponseData = json_decode($userResponse, true);
    if ($userResponseData) {
        if (isset($userResponseData['success']) && $userResponseData['success']) {
            echo "✅ Protected Route Access: SUCCESS\n";
            if (isset($userResponseData['data']['user']['name'])) {
                echo "✅ User Name: " . $userResponseData['data']['user']['name'] . "\n";
            }
        } else {
            echo "❌ Protected Route Access: FAILED\n";
        }
    } else {
        echo "❌ Could not parse JSON response\n";
    }
} else {
    echo "❌ Skipping protected route test - no valid token\n";
}

echo "\n=== Test Summary ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Target API: https://farmersnetwork.ng/\n";

$regStatus = (isset($regResponseData['success']) && $regResponseData['success']) ? '✅ SUCCESS' : '❌ FAILED';
$loginStatus = (isset($loginResponseData['success']) && $loginResponseData['success']) ? '✅ SUCCESS' : '❌ FAILED';

echo "Registration: $regStatus\n";
echo "Login: $loginStatus\n";

echo "\nTest completed successfully!\n";
?>
