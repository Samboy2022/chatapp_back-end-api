<?php
// Simple test script to verify Laravel is working
echo "=== Laravel Server Test ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Test if Laravel is accessible
try {
    // Check if we can load Laravel
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    
    echo "✅ Laravel Bootstrap: SUCCESS\n";
    
    // Test database connection
    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $pdo = DB::connection()->getPdo();
        echo "✅ Database Connection: SUCCESS\n";
    } catch (Exception $e) {
        echo "❌ Database Connection: FAILED - " . $e->getMessage() . "\n";
    }
    
    // Test API route
    echo "\n=== Testing API Routes ===\n";
    echo "Test this URL in your browser:\n";
    echo "http://127.0.0.1:8000/api/test\n";
    echo "http://192.168.55.83:8000/api/test\n";
    
} catch (Exception $e) {
    echo "❌ Laravel Bootstrap: FAILED - " . $e->getMessage() . "\n";
}

echo "\n=== Instructions ===\n";
echo "1. Run: php artisan serve --host=0.0.0.0 --port=8000\n";
echo "2. Test in browser: http://127.0.0.1:8000/api/test\n";
echo "3. Test mobile access: http://192.168.55.83:8000/api/test\n";
echo "4. Both should return JSON response\n";
?>
