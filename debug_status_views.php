<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StatusView;
use App\Models\User;

echo "Debugging Status Views Data\n";
echo "===========================\n\n";

try {
    // Get recent status views
    $views = StatusView::with('viewer')->latest()->take(5)->get();
    
    echo "Total status views found: " . $views->count() . "\n\n";
    
    foreach ($views as $view) {
        echo "View ID: {$view->id}\n";
        echo "Status ID: {$view->status_id}\n";
        echo "Viewer ID: {$view->viewer_id}\n";
        echo "Viewer exists: " . ($view->viewer ? 'YES' : 'NO') . "\n";
        
        if ($view->viewer) {
            echo "Viewer name: {$view->viewer->name}\n";
            echo "Viewer email: {$view->viewer->email}\n";
        } else {
            // Check if user exists in database
            $user = User::find($view->viewer_id);
            if ($user) {
                echo "User exists in DB: {$user->name} ({$user->email})\n";
            } else {
                echo "User NOT found in database!\n";
            }
        }
        
        echo "Viewed at: {$view->viewed_at}\n";
        echo "Created at: {$view->created_at}\n";
        echo "---\n\n";
    }
    
    // Check users table
    echo "Recent users:\n";
    $users = User::latest()->take(5)->get(['id', 'name', 'email']);
    foreach ($users as $user) {
        echo "User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
