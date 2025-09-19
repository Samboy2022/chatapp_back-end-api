<?php
// Test login API endpoint
echo "=== Laravel Login API Test ===\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "1. Checking if test user exists...\n";

// Find a user to test with
$testUser = User::first();

if (!$testUser) {
    echo "❌ No users found in database. Creating a test user...\n";
    
    try {
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => bcrypt('password'),
            'is_active' => 1
        ]);
        echo "✅ Test user created successfully!\n";
    } catch (Exception $e) {
        echo "❌ Failed to create test user: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "✅ Found test user: {$testUser->name} ({$testUser->phone_number})\n";
}

echo "\n2. Testing login API endpoint...\n";

// Test the login endpoint
try {
    // Create a fake request to test login
    $loginData = [
        'login' => $testUser->phone_number, // Use phone number
        'password' => 'password' // Default password
    ];

    echo "Login data being sent: " . json_encode($loginData) . "\n";

    // Create request with JSON content
    $request = Illuminate\Http\Request::create(
        '/api/auth/login',
        'POST',
        [], // parameters
        [], // cookies
        [], // files
        [], // server
        json_encode($loginData) // content
    );
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');

    echo "Request data received: " . json_encode($request->all()) . "\n";
    echo "Request JSON: " . $request->getContent() . "\n";
    
    $response = $app->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Login API: SUCCESS\n";
        
        $responseData = json_decode($response->getContent(), true);
        if (isset($responseData['data']['token'])) {
            echo "✅ Token generated: " . substr($responseData['data']['token'], 0, 20) . "...\n";
        }
    } else {
        echo "❌ Login API: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "❌ Login API Test: FAILED - " . $e->getMessage() . "\n";
    echo "Error Details: " . $e->getTraceAsString() . "\n";
}

echo "\n3. Testing with email login...\n";

if ($testUser->email) {
    try {
        $loginData = [
            'login' => $testUser->email, // Use email
            'password' => 'password'
        ];
        
        $request = Illuminate\Http\Request::create('/api/auth/login', 'POST', $loginData);
        $request->headers->set('Accept', 'application/json');
        
        $response = $app->handle($request);
        
        echo "Email Login Status: " . $response->getStatusCode() . "\n";
        
        if ($response->getStatusCode() === 200) {
            echo "✅ Email Login: SUCCESS\n";
        } else {
            echo "❌ Email Login: FAILED\n";
            echo "Response: " . $response->getContent() . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Email Login Test: FAILED - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "Test User Details:\n";
echo "Phone: {$testUser->phone_number}\n";
echo "Email: {$testUser->email}\n";
echo "Password: password\n";
echo "\nUse these credentials to test login in your mobile app!\n";
?>
