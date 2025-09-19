<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\WebSocketController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public test route for connection testing
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API connection successful!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Health check endpoints (public)
Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/broadcast', [HealthController::class, 'broadcast']);

// App configuration endpoints (public)
Route::get('/app-config', [AppConfigController::class, 'getConfig']);
Route::get('/app-config/validate', [AppConfigController::class, 'validateConfig']);
Route::post('/app-config/clear-cache', [AppConfigController::class, 'clearCache']);
Route::get('/app-config/history', [AppConfigController::class, 'getConfigHistory']);

// Broadcast settings endpoints (public for mobile apps)
Route::prefix('broadcast-settings')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'index']);
    Route::get('/connection-info', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'connectionInfo']);
    Route::get('/status', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'status']);
    Route::get('/health', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'health']);
    Route::post('/test', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'testConnection']);
    Route::get('/call-signaling', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'callSignalingConfig']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/privacy', [AuthController::class, 'updatePrivacy']);
    });

    // Contact routes
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/sync', [ContactController::class, 'sync']);
        Route::get('/blocked', [ContactController::class, 'getBlocked']);
        Route::get('/favorites', [ContactController::class, 'getFavorites']);
        Route::get('/search', [ContactController::class, 'search']);
        Route::post('/block/{contactId}', [ContactController::class, 'block']);
        Route::post('/unblock/{contactId}', [ContactController::class, 'unblock']);
        Route::post('/favorite/{contactId}', [ContactController::class, 'toggleFavorite']);
    });

    // Chat routes
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
    });
    
    // Chat message routes - MUST come before general chat routes to avoid conflicts
    Route::prefix('chats/{chatId}/messages')->group(function () {
        Route::get('/', [MessageController::class, 'index']);
        Route::post('/', [MessageController::class, 'store']);
        Route::get('/{messageId}', [MessageController::class, 'show']);
        Route::put('/{messageId}', [MessageController::class, 'update']);
        Route::delete('/{messageId}', [MessageController::class, 'destroy']);
        Route::post('/{messageId}/read', [MessageController::class, 'markAsRead']);
        Route::post('/{messageId}/react', [MessageController::class, 'react']);
        Route::delete('/{messageId}/react', [MessageController::class, 'removeReaction']);
    });
    
    // Chat management routes - MUST come after message routes
    Route::prefix('chats/{chatId}')->group(function () {
        Route::get('/', [ChatController::class, 'show']);
        Route::put('/', [ChatController::class, 'update']);
        Route::post('/archive', [ChatController::class, 'archive']);
        Route::post('/pin', [ChatController::class, 'pin']);
        Route::post('/mute', [ChatController::class, 'mute']);
        Route::post('/leave', [ChatController::class, 'leave']);
    });

    // Message routes (global) - P2P messaging
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'getAllMessages']); // Get all user messages
        Route::post('/', [MessageController::class, 'sendMessage']); // Send P2P message
        Route::get('/{userId}', [MessageController::class, 'getConversation']); // Get conversation with specific user
        Route::delete('/{messageId}', [MessageController::class, 'destroy']);
        Route::post('/{messageId}/read', [MessageController::class, 'markAsRead']);
        Route::post('/{messageId}/react', [MessageController::class, 'addReaction']); // Add reaction
        Route::delete('/{messageId}/react', [MessageController::class, 'removeReaction']); // Remove reaction
    });

    // Status routes
    Route::prefix('status')->group(function () {
        Route::get('/', [StatusController::class, 'index']);
        Route::post('/', [StatusController::class, 'store']);
        Route::get('/user/{userId}', [StatusController::class, 'getUserStatuses']);
        Route::post('/{statusId}/view', [StatusController::class, 'markAsViewed']);
        Route::get('/{statusId}/viewers', [StatusController::class, 'getViewers']);
        Route::delete('/{statusId}', [StatusController::class, 'destroy']);
    });

    // Alternative status routes (plural)
    Route::prefix('statuses')->group(function () {
        Route::get('/', [StatusController::class, 'index']);
        Route::post('/', [StatusController::class, 'store']);
        Route::get('/{statusId}', [StatusController::class, 'show']);
        Route::get('/{statusId}/views', [StatusController::class, 'getViewers']);
        Route::post('/{statusId}/view', [StatusController::class, 'markAsViewed']);
        Route::delete('/{statusId}', [StatusController::class, 'destroy']);
    });

    // Group routes
    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store']);
        Route::get('/{groupId}', [GroupController::class, 'show']);
        Route::post('/{groupId}/users', [GroupController::class, 'addUser']); // Add single user
        Route::post('/{groupId}/members', [GroupController::class, 'addMembers']); // Add multiple users
        Route::delete('/{groupId}/users/{userId}', [GroupController::class, 'removeUser']); // Remove single user
        Route::delete('/{groupId}/members/{memberId}', [GroupController::class, 'removeMember']);
        Route::post('/{groupId}/message', [GroupController::class, 'sendMessage']); // Send group message
        Route::post('/{groupId}/leave', [GroupController::class, 'leave']);
    });

    // Search routes
    Route::prefix('search')->group(function () {
        Route::get('/users', [SearchController::class, 'searchUsers']);
        Route::get('/messages', [SearchController::class, 'searchMessages']);
    });

    // User search routes (alternative endpoints)
    Route::prefix('users')->group(function () {
        Route::get('/search', [SearchController::class, 'searchUsers']);
        Route::get('/search/phone', [SearchController::class, 'searchByPhone']);
        Route::get('/search/email', [SearchController::class, 'searchByEmail']);
    });

    // Chat creation route
    Route::post('/chats/create-or-get', [SearchController::class, 'createOrGetChat']);

    // Media routes
    Route::prefix('media')->group(function () {
        Route::post('/upload', [MediaController::class, 'upload']);
        Route::post('/upload/avatar', [MediaController::class, 'uploadAvatar']);
        Route::post('/upload/chat-avatar', [MediaController::class, 'uploadChatAvatar']);
        Route::post('/upload/status', [MediaController::class, 'uploadStatusMedia']);
        Route::delete('/delete', [MediaController::class, 'delete']);
    });

    // Call routes
    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::get('/active', [CallController::class, 'getActiveCalls']);
        Route::post('/', [CallController::class, 'initiate']);
        Route::get('/statistics', [CallController::class, 'getStatistics']);
        Route::get('/missed-count', [CallController::class, 'getMissedCallsCount']);
        Route::get('/cleanup-stale', [CallController::class, 'cleanupStaleRingingCalls']);
        Route::post('/{callId}/accept', [CallController::class, 'accept']); // Accept call
        Route::post('/{callId}/answer', [CallController::class, 'answer']); // Answer call (alias)
        Route::post('/{callId}/reject', [CallController::class, 'reject']); // Reject call
        Route::post('/{callId}/decline', [CallController::class, 'decline']); // Decline call (alias)
        Route::post('/{callId}/end', [CallController::class, 'end']);
        Route::get('/{callId}', [CallController::class, 'show']);
    });

    // User settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/profile', [SettingsController::class, 'getProfile']);
        Route::put('/profile', [SettingsController::class, 'updateProfile']);
        Route::get('/privacy', [SettingsController::class, 'getPrivacy']);
        Route::put('/privacy', [SettingsController::class, 'updatePrivacy']);
        Route::get('/media-settings', [SettingsController::class, 'getMediaSettings']);
        Route::put('/media-settings', [SettingsController::class, 'updateMediaSettings']);
        Route::get('/notifications', [SettingsController::class, 'getNotificationSettings']);
        Route::put('/notifications', [SettingsController::class, 'updateNotificationSettings']);
        Route::delete('/delete-account', [SettingsController::class, 'deleteAccount']);
        Route::get('/export-data', [SettingsController::class, 'exportData']);
    });

    // WebSocket routes
    Route::prefix('websocket')->group(function () {
        Route::get('/connection-info', [WebSocketController::class, 'getConnectionInfo']);
        Route::get('/active-chats', [WebSocketController::class, 'getActiveChats']);
        Route::post('/online-status', [WebSocketController::class, 'updateOnlineStatus']);
        Route::post('/chats/{chatId}/typing', [WebSocketController::class, 'typing']);
        Route::post('/messages/{messageId}/read', [WebSocketController::class, 'markMessageAsRead']);
    });

    // Broadcast settings admin routes (authenticated)
    Route::prefix('broadcast-settings')->group(function () {
        Route::post('/update', [App\Http\Controllers\Api\BroadcastSettingsController::class, 'update']);
    });

    // Test route for checking authentication (authenticated users only)
    Route::get('/test-auth', function (Request $request) {
        return response()->json([
            'message' => 'Authenticated API is working!',
            'user' => $request->user(),
            'timestamp' => now()
        ]);
    });
});

// App settings routes (public - no authentication required)
Route::prefix('app-settings')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\AppSettingsController::class, 'index']);
    Route::get('/config', [App\Http\Controllers\Api\AppSettingsController::class, 'getAppConfig']);
    Route::get('/version', [App\Http\Controllers\Api\AppSettingsController::class, 'getVersion']);
    Route::get('/groups', [App\Http\Controllers\Api\AppSettingsController::class, 'getGroups']);
    Route::get('/group/{group}', [App\Http\Controllers\Api\AppSettingsController::class, 'getByGroup']);
    Route::get('/{key}', [App\Http\Controllers\Api\AppSettingsController::class, 'getByKey']);
    Route::post('/multiple', [App\Http\Controllers\Api\AppSettingsController::class, 'getMultiple']);
});

// Admin API routes for testing (no CSRF protection)
Route::prefix('admin')->group(function () {
    Route::post('settings/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache']);
    Route::post('settings/optimize', [App\Http\Controllers\Admin\SettingController::class, 'optimizeSystem']);
    Route::post('settings/test-email', [App\Http\Controllers\Admin\SettingController::class, 'testEmail']);
    Route::post('settings/backup', [App\Http\Controllers\Admin\SettingController::class, 'backupDatabase']);
    Route::get('settings/export', [App\Http\Controllers\Admin\SettingController::class, 'exportSettings']);
    Route::post('settings/import', [App\Http\Controllers\Admin\SettingController::class, 'importSettings']);
    Route::post('settings/update', [App\Http\Controllers\Admin\SettingController::class, 'update']);

    // Call monitoring API routes
    Route::get('calls/active', [App\Http\Controllers\Admin\CallController::class, 'activeCalls']);
    Route::get('calls/realtime-stats', [App\Http\Controllers\Admin\CallController::class, 'realtimeStats']);
    Route::get('calls/recent-activity', [App\Http\Controllers\Admin\CallController::class, 'recentActivity']);
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API route not found'
    ], 404);
});