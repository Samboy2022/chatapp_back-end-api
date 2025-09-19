<?php

echo "🔧 Testing Database Backup Debug\n";
echo "================================\n\n";

// Test mysqldump availability
echo "1. Testing mysqldump availability...\n";
$output = [];
$returnVar = 0;
exec('mysqldump --version 2>&1', $output, $returnVar);

echo "Return code: $returnVar\n";
echo "Output: " . implode("\n", $output) . "\n";

if ($returnVar === 0) {
    echo "✅ mysqldump is available\n";
} else {
    echo "❌ mysqldump is not available\n";
}

echo "\n2. Testing database connection...\n";

// Test database connection
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $dbConfig = config('database.connections.' . config('database.default'));
    echo "Database: " . $dbConfig['database'] . "\n";
    echo "Host: " . $dbConfig['host'] . "\n";
    echo "Port: " . ($dbConfig['port'] ?? 3306) . "\n";
    echo "Username: " . $dbConfig['username'] . "\n";
    
    // Test connection
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port=" . ($dbConfig['port'] ?? 3306) . ";dbname={$dbConfig['database']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    
    echo "✅ Database connection successful\n";
    
    // Get table count
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "Tables found: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n3. Testing backup API endpoint...\n";

$url = 'http://127.0.0.1:8000/api/admin/settings/backup';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response:\n";
    echo $response . "\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "\nParsed response:\n";
        print_r($data);
    }
}

echo "\n🎯 Debug test completed!\n";
