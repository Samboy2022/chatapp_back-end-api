<?php

$url = 'http://127.0.0.1:8000/api/app-settings/version';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Testing version endpoint: $url\n";
echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response:\n";
    echo $response . "\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "\nParsed data:\n";
        print_r($data);
    }
}
