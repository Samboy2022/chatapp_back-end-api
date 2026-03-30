<?php
require __DIR__ . '/../vendor/autoload.php';
// bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::first();
if (!$user) {
    echo "NOUSER\n";
    exit(1);
}
$token = $user->createToken('test-token')->plainTextToken;
echo $token . "\n";
