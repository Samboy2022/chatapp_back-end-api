<?php
require __DIR__ . '/../vendor/autoload.php';
// bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StreamService;
use App\Models\User;

try {
    $user = User::first();
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No users found to test with']);
        exit(1);
    }

    /** @var StreamService $stream */
    $stream = $app->make(StreamService::class);

    $token = $stream->createUserToken($user->id);
    $config = $stream->getConfig();

    $response = [
        'success' => true,
        'data' => [
            'token' => $token,
            'api_key' => $config['api_key'] ?? null,
            'user_id' => $user->id,
            'expires_at' => date('c', strtotime('+24 hours')),
        ],
        'message' => 'Stream video token generated (direct test)'
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    exit(1);
}
