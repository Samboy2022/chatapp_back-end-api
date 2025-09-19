<?php

namespace App\Services;

use App\Models\User;
use App\Models\Chat;
use App\Events\UserOnlineStatusChanged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    /**
     * Handle user connection
     */
    public function handleUserConnect(User $user): void
    {
        try {
            // Update user online status
            $user->update([
                'is_online' => true,
                'last_seen_at' => now(),
            ]);

            // Store connection in cache for tracking
            Cache::put("websocket_user_{$user->id}", [
                'connected_at' => now(),
                'last_activity' => now(),
            ], now()->addHours(24));

            // Broadcast online status
            broadcast(new UserOnlineStatusChanged($user, true));

            Log::info("User {$user->id} connected to WebSocket");

        } catch (\Exception $e) {
            Log::error("Error handling user connect: " . $e->getMessage());
        }
    }

    /**
     * Handle user disconnection
     */
    public function handleUserDisconnect(User $user): void
    {
        try {
            // Update user offline status
            $user->update([
                'is_online' => false,
                'last_seen_at' => now(),
            ]);

            // Remove connection from cache
            Cache::forget("websocket_user_{$user->id}");

            // Broadcast offline status
            broadcast(new UserOnlineStatusChanged($user, false));

            Log::info("User {$user->id} disconnected from WebSocket");

        } catch (\Exception $e) {
            Log::error("Error handling user disconnect: " . $e->getMessage());
        }
    }

    /**
     * Update user activity
     */
    public function updateUserActivity(User $user): void
    {
        try {
            // Update last activity in cache
            Cache::put("websocket_user_{$user->id}", [
                'connected_at' => Cache::get("websocket_user_{$user->id}.connected_at", now()),
                'last_activity' => now(),
            ], now()->addHours(24));

            // Update last seen
            $user->update(['last_seen_at' => now()]);

        } catch (\Exception $e) {
            Log::error("Error updating user activity: " . $e->getMessage());
        }
    }

    /**
     * Check if user is connected
     */
    public function isUserConnected(int $userId): bool
    {
        return Cache::has("websocket_user_{$userId}");
    }

    /**
     * Get connected users count
     */
    public function getConnectedUsersCount(): int
    {
        $pattern = "websocket_user_*";
        $keys = Cache::getRedis()->keys($pattern);
        return count($keys);
    }

    /**
     * Get user's chat channels for subscription
     */
    public function getUserChatChannels(User $user): array
    {
        $channels = [];

        // Personal channel
        $channels[] = "private-user.{$user->id}";

        // Chat channels
        foreach ($user->chats as $chat) {
            $channels[] = "private-chat.{$chat->id}";
            $channels[] = "presence-chat.{$chat->id}";
        }

        // Global presence channel
        $channels[] = "presence-users";

        return $channels;
    }

    /**
     * Clean up stale connections
     */
    public function cleanupStaleConnections(): void
    {
        try {
            $pattern = "websocket_user_*";
            $keys = Cache::getRedis()->keys($pattern);

            $staleThreshold = now()->subMinutes(5);

            foreach ($keys as $key) {
                $data = Cache::get($key);
                
                if ($data && isset($data['last_activity'])) {
                    $lastActivity = \Carbon\Carbon::parse($data['last_activity']);
                    
                    if ($lastActivity->lt($staleThreshold)) {
                        // Extract user ID from key
                        $userId = str_replace('websocket_user_', '', $key);
                        $user = User::find($userId);
                        
                        if ($user) {
                            $this->handleUserDisconnect($user);
                        }
                    }
                }
            }

            Log::info("Cleaned up stale WebSocket connections");

        } catch (\Exception $e) {
            Log::error("Error cleaning up stale connections: " . $e->getMessage());
        }
    }

    /**
     * Get WebSocket server status
     */
    public function getServerStatus(): array
    {
        return [
            'connected_users' => $this->getConnectedUsersCount(),
            'server_uptime' => Cache::get('websocket_server_start_time', now()),
            'last_cleanup' => Cache::get('websocket_last_cleanup', null),
        ];
    }

    /**
     * Broadcast to specific chat
     */
    public function broadcastToChat(Chat $chat, string $event, array $data): void
    {
        try {
            broadcast(new \Illuminate\Broadcasting\GenericBroadcastEvent($event, $data))
                ->toChannel("private-chat.{$chat->id}");

        } catch (\Exception $e) {
            Log::error("Error broadcasting to chat {$chat->id}: " . $e->getMessage());
        }
    }

    /**
     * Broadcast to specific user
     */
    public function broadcastToUser(User $user, string $event, array $data): void
    {
        try {
            broadcast(new \Illuminate\Broadcasting\GenericBroadcastEvent($event, $data))
                ->toChannel("private-user.{$user->id}");

        } catch (\Exception $e) {
            Log::error("Error broadcasting to user {$user->id}: " . $e->getMessage());
        }
    }
}
