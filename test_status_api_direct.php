<?php

/**
 * Direct API test to debug status viewer response
 */

$baseUrl = 'http://127.0.0.1:8000/api';

// Login first
$loginData = [
    'login' => 'admin@chatapp.com',
    'password' => 'password'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/auth/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($loginData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$loginResponse = json_decode($response, true);
curl_close($ch);

if (!$loginResponse['success']) {
    echo "Login failed\n";
    exit;
}

$token = $loginResponse['data']['token'];
echo "Login successful, token: " . substr($token, 0, 20) . "...\n\n";

// Get recent status ID
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Status;

$recentStatus = Status::latest()->first();
if (!$recentStatus) {
    echo "No status found\n";
    exit;
}

$statusId = $recentStatus->id;
echo "Testing with status ID: {$statusId}\n\n";

// Test the viewers endpoint
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . "/status/{$statusId}/viewers",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Raw Response:\n";
echo $response . "\n\n";

$decodedResponse = json_decode($response, true);
echo "Decoded Response:\n";
print_r($decodedResponse);

if (isset($decodedResponse['data']) && is_array($decodedResponse['data'])) {
    echo "\nViewer data analysis:\n";
    foreach ($decodedResponse['data'] as $index => $viewer) {
        echo "Viewer {$index}: ";
        if (is_null($viewer)) {
            echo "NULL\n";
        } else {
            echo "Type: " . gettype($viewer) . "\n";
            if (is_array($viewer)) {
                echo "Keys: " . implode(', ', array_keys($viewer)) . "\n";
                echo "Data: " . json_encode($viewer) . "\n";
            }
        }
    }
}
