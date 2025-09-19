<?php
// Simple API test script
echo "=== Laravel API Test ===\n";

// Test 1: Check if Laravel can bootstrap
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel Bootstrap: SUCCESS\n";
} catch (Exception $e) {
    echo "❌ Laravel Bootstrap: FAILED - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test the API route directly
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Create a fake request to test the route
    $request = Illuminate\Http\Request::create('/api/test', 'GET');
    $request->headers->set('Accept', 'application/json');
    
    $response = $app->handle($request);
    
    echo "✅ API Route Test: SUCCESS\n";
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "❌ API Route Test: FAILED - " . $e->getMessage() . "\n";
    echo "Error Details: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "If the API route test succeeded, your Laravel API is working!\n";
echo "Next steps:\n";
echo "1. Start server: php artisan serve --host=0.0.0.0 --port=8000\n";
echo "2. Test in browser: http://127.0.0.1:8000/api/test\n";
echo "3. Test mobile access: http://192.168.55.83:8000/api/test\n";
?>
