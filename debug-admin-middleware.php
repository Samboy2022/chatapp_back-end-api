<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Middleware\AdminAuth;

echo "=== Admin Middleware Debug ===\n\n";

// Test 1: Check session without admin login
echo "1. Testing session without admin login\n";
session()->flush(); // Clear any existing session
echo "   - admin_logged_in: " . (session('admin_logged_in') ? 'true' : 'false') . "\n";
echo "   - Session ID: " . session()->getId() . "\n";

// Test 2: Simulate admin login
echo "\n2. Simulating admin login\n";
session([
    'admin_logged_in' => true,
    'admin_user_id' => 1,
    'admin_user' => [
        'id' => 1,
        'name' => 'Admin User',
        'email' => 'admin@chatapp.com'
    ]
]);

echo "   - admin_logged_in: " . (session('admin_logged_in') ? 'true' : 'false') . "\n";
echo "   - admin_user: " . json_encode(session('admin_user')) . "\n";

// Test 3: Test middleware logic directly
echo "\n3. Testing middleware logic directly\n";

$middleware = new AdminAuth();
$request = Request::create('/admin/users', 'GET');

try {
    $response = $middleware->handle($request, function($req) {
        return response('Middleware passed', 200);
    });
    
    echo "   - Middleware with session: " . $response->getStatusCode() . " - " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "   - Middleware error: " . $e->getMessage() . "\n";
}

// Test 4: Test middleware without session
echo "\n4. Testing middleware without session\n";
session()->flush(); // Clear session

try {
    $response = $middleware->handle($request, function($req) {
        return response('Middleware passed', 200);
    });
    
    echo "   - Middleware without session: " . $response->getStatusCode() . "\n";
    echo "   - Response type: " . get_class($response) . "\n";
    
    if (method_exists($response, 'getTargetUrl')) {
        echo "   - Redirect URL: " . $response->getTargetUrl() . "\n";
    }
} catch (Exception $e) {
    echo "   - Middleware error: " . $e->getMessage() . "\n";
}

// Test 5: Check route middleware registration
echo "\n5. Checking route middleware registration\n";
$router = app('router');
$middlewareGroups = $router->getMiddlewareGroups();
$middleware = $router->getMiddleware();

echo "   - Admin auth middleware registered: " . (isset($middleware['admin.auth']) ? 'YES' : 'NO') . "\n";
if (isset($middleware['admin.auth'])) {
    echo "   - Middleware class: " . $middleware['admin.auth'] . "\n";
}

echo "   - Web middleware group: " . (isset($middlewareGroups['web']) ? 'YES' : 'NO') . "\n";

echo "\n=== Debug Complete ===\n";
