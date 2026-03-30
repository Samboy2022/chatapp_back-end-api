<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User private channel - for personal notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Chat private channel - for chat messages
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    
    if (!$chat) {
        return false;
    }
    
    // Check if user is a participant in this chat
    return $chat->participants()->where('user_id', $user->id)->exists();
});

// Presence channel for chat - for typing indicators and online status
Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    
    if (!$chat) {
        return false;
    }
    
    // Check if user is a participant in this chat
    if ($chat->participants()->where('user_id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'is_online' => true,
            'last_seen_at' => now()->toISOString(),
        ];
    }
    
    return false;
});

// Global presence channel for user status
Broadcast::channel('presence-users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar_url' => $user->avatar_url,
        'is_online' => $user->is_online,
        'last_seen_at' => $user->last_seen_at,
    ];
});
