<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\CallController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ApiDocumentationController;
use App\Http\Controllers\Api\BroadcastSettingsController;
use App\Http\Controllers\ApiTesterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// Landing Page Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Fallback login route (redirects to admin login)
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Fallback register route (redirects to admin login)
Route::get('/register', function () {
    return redirect()->route('admin.login');
})->name('register');

// Footer Pages
Route::get('/help-center', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('help-center', compact('appSettings'));
})->name('help-center');

Route::get('/privacy-policy', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('privacy-policy', compact('appSettings'));
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('terms-of-service', compact('appSettings'));
})->name('terms-of-service');

Route::get('/about-us', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('about-us', compact('appSettings'));
})->name('about-us');

Route::get('/careers', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('careers', compact('appSettings'));
})->name('careers');

Route::get('/press', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('press', compact('appSettings'));
})->name('press');

Route::get('/contact', function () {
    $appSettings = \App\Models\Setting::getSettings();
    return view('contact', compact('appSettings'));
})->name('contact');

// Host Fix Routes (Temporary Tool for Shared Hosting)
Route::prefix('host-fix')->group(function () {
    Route::get('/status', [App\Http\Controllers\HostFixController::class, 'checkStatus']);
    Route::get('/symlink', [App\Http\Controllers\HostFixController::class, 'fixSymlink']);
    Route::get('/update-url', [App\Http\Controllers\HostFixController::class, 'updateAppUrl']);
    Route::get('/normalize-urls', [App\Http\Controllers\HostFixController::class, 'normalizeUrls']);
    Route::get('/test-upload', [App\Http\Controllers\HostFixController::class, 'testUpload']);
});

// Broadcasting Authentication Routes (for WebSocket authentication)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Public Admin API Routes (for testing)
Route::prefix('admin-api')->group(function () {
    Route::get('calls/active', [App\Http\Controllers\Admin\CallController::class, 'activeCalls']);
    Route::get('calls/realtime-stats', [App\Http\Controllers\Admin\CallController::class, 'realtimeStats']);
    Route::get('calls/recent-activity', [App\Http\Controllers\Admin\CallController::class, 'recentActivity']);

    // Settings system tools (for testing)
    Route::post('settings/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache']);
    Route::post('settings/optimize', [App\Http\Controllers\Admin\SettingController::class, 'optimizeSystem']);
    Route::post('settings/test-email', [App\Http\Controllers\Admin\SettingController::class, 'testEmail']);
    Route::post('settings/backup', [App\Http\Controllers\Admin\SettingController::class, 'backupDatabase']);
    Route::get('settings/export', [App\Http\Controllers\Admin\SettingController::class, 'exportSettings']);
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin Panel Routes (Protected)
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/system-health', [DashboardController::class, 'systemHealth'])->name('system-health');
    
    // API Documentation
    Route::prefix('api-documentation')->name('api-documentation.')->group(function () {
        Route::get('/', [ApiDocumentationController::class, 'index'])->name('index');
        Route::get('/endpoints', [ApiDocumentationController::class, 'endpoints'])->name('endpoints');
        Route::get('/authentication', [ApiDocumentationController::class, 'authentication'])->name('authentication');
        Route::get('/examples', [ApiDocumentationController::class, 'examples'])->name('examples');
        Route::get('/configuration', [ApiDocumentationController::class, 'configuration'])->name('configuration');
        Route::get('/testing', [ApiDocumentationController::class, 'testing'])->name('testing');
    });
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::patch('users/{userId}/toggle-block', [App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('users.toggle-block')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
    // Chats Management
    Route::resource('chats', ChatController::class);
    Route::patch('chats/{chat}/toggle-active', [ChatController::class, 'toggleActive'])->name('chats.toggleActive');
    Route::post('chats/{chat}/add-participant', [ChatController::class, 'addParticipant'])->name('chats.add-participant');
    Route::delete('chats/{chat}/remove-participant/{user}', [ChatController::class, 'removeParticipant'])->name('chats.remove-participant');
    Route::get('chats/export', [ChatController::class, 'export'])->name('chats.export');
    
    // Messages Management
    Route::resource('messages', MessageController::class);
    Route::post('messages/{id}/restore', [MessageController::class, 'restore'])->name('messages.restore');
    Route::delete('messages/{message}/force-delete', [MessageController::class, 'forceDelete'])->name('messages.force-delete');
    Route::post('messages/{message}/moderate', [MessageController::class, 'moderate'])->name('messages.moderate');
    Route::get('messages/export', [MessageController::class, 'export'])->name('messages.export');
    
    // Status Updates Management
    Route::resource('statuses', StatusController::class);
    Route::post('statuses/cleanup-expired', [StatusController::class, 'cleanupExpired'])->name('statuses.cleanup-expired');
    Route::post('statuses/{status}/extend', [StatusController::class, 'extend'])->name('statuses.extend');
    Route::get('statuses/export', [StatusController::class, 'export'])->name('statuses.export');
    
    // Calls Management

    // Real-time Call Monitoring (must be defined BEFORE resource routes)
    Route::get('calls/active', [CallController::class, 'activeCalls'])->name('calls.active');
    Route::get('calls/realtime-stats', [CallController::class, 'realtimeStats'])->name('calls.realtime-stats');
    Route::get('calls/recent-activity', [CallController::class, 'recentActivity'])->name('calls.recent-activity');
    Route::get('calls/statistics', [CallController::class, 'statistics'])->name('calls.statistics');
    Route::get('calls/export', [CallController::class, 'export'])->name('calls.export');

    // Call resource routes (must be defined AFTER specific routes)
    Route::resource('calls', CallController::class);
    Route::post('calls/{call}/end', [CallController::class, 'endCall'])->name('calls.end');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/activity', [ReportController::class, 'activity'])->name('activity');
        Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/update', [SettingController::class, 'update'])->name('update');
        Route::get('/api/all', [SettingController::class, 'getSettings'])->name('api.all');
        Route::post('/api/update-single', [SettingController::class, 'updateSingle'])->name('api.update-single');
        Route::post('/api/create', [SettingController::class, 'create'])->name('api.create');
        Route::post('/api/delete', [SettingController::class, 'delete'])->name('api.delete');
        Route::post('/upload-logo', [SettingController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/upload-firebase-json', [SettingController::class, 'uploadFirebaseJson'])->name('upload-firebase-json');
        Route::post('/remove-logo', [SettingController::class, 'removeLogo'])->name('remove-logo');
        Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [SettingController::class, 'optimizeSystem'])->name('optimize');
        Route::post('/backup', [SettingController::class, 'backupDatabase'])->name('backup');
        Route::post('/test-email', [SettingController::class, 'testEmail'])->name('test-email');
        Route::get('/export', [SettingController::class, 'exportSettings'])->name('export');
        Route::post('/import', [SettingController::class, 'importSettings'])->name('import');
        Route::post('/reset', [SettingController::class, 'resetSettings'])->name('reset');
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('index');
        Route::put('/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
        Route::put('/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password');
    });

    // Broadcast Settings (Redirect to new realtime settings)
    Route::prefix('broadcast-settings')->name('broadcast-settings.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.realtime-settings.index');
        })->name('index');

        // Redirect all old routes to new system
        Route::get('/{any}', function () {
            return redirect()->route('admin.realtime-settings.index');
        })->where('any', '.*');
    });

    // Realtime Settings (Primary System)
    Route::prefix('realtime-settings')->name('realtime-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RealtimeSettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\RealtimeSettingsController::class, 'update'])->name('update');
        Route::get('/status', [App\Http\Controllers\Admin\RealtimeSettingsController::class, 'status'])->name('status');
        Route::post('/test', [App\Http\Controllers\Admin\RealtimeSettingsController::class, 'testConnection'])->name('test');
        Route::post('/reset', [App\Http\Controllers\Admin\RealtimeSettingsController::class, 'reset'])->name('reset');
    });

    // AI Settings (Farming Assistant)
    Route::prefix('ai-settings')->name('ai-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AiSettingController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\AiSettingController::class, 'store'])->name('store');
        Route::put('/{id}', [App\Http\Controllers\Admin\AiSettingController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\AiSettingController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/activate', [App\Http\Controllers\Admin\AiSettingController::class, 'activate'])->name('activate');
        Route::post('/test', [App\Http\Controllers\Admin\AiSettingController::class, 'testConnection'])->name('test');
        Route::get('/api/all', [App\Http\Controllers\Admin\AiSettingController::class, 'getSettings'])->name('api.all');
        Route::post('/chat', [App\Http\Controllers\Admin\AiSettingController::class, 'chat'])->name('chat');
    });
});

// Test route for Pusher broadcasting
Route::get('/test-pusher', function () {
    try {
        // Test direct Pusher connection without queues
        $pusher = new \Pusher\Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        $result = $pusher->trigger('test-channel', 'test.event', [
            'message' => 'Hello from direct Pusher test!',
            'timestamp' => now()->toISOString(),
            'source' => 'Direct Laravel Test'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Direct Pusher test successful',
            'result' => $result,
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to broadcast: ' . $e->getMessage(),
            'error' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.pusher');
