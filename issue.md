# 🔍 Rigorous Code Review: Agora Video/Audio Call System

## Summary of Issues Reported
1. When User A calls User B, the call screen **does NOT show** on User B's device (but it rings)
2. When User A ends the call, it **keeps ringing in the background** until forcefully ended via admin
3. The call is **NOT connecting** between User A and User B
4. User B's screen is **NOT waking up**, not showing the incoming call UI, and cannot answer/decline

---

## 🚨 CRITICAL BUGS FOUND

### Bug #1: `call.{userId}` Broadcast Channel Is NOT Authorized

> [!CAUTION]
> **This is the #1 root cause of calls not showing, not connecting, and not being endable.**

Your broadcast events (`CallInitiated`, `CallEnded`, `CallAccepted`, `CallRejected`) all broadcast to:

```php
new PrivateChannel('call.' . $this->recipient->id)
```

But in [channels.php](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/routes/channels.php), **there is NO authorization for the `call.{userId}` channel**. The file only defines:
- `user.{userId}`
- `chat.{chatId}`
- `presence-chat.{chatId}`
- `presence-users`

**Result:** Every WebSocket broadcast for call events is **silently rejected** by Laravel because the channel is unauthorized. User B's Flutter app **never receives** the `CallInitiated` event via WebSocket. The only thing that works is the FCM push notification, which explains why the phone "rings" (the notification arrives) but the call UI doesn't show properly.

**Fix — Add to** `routes/channels.php`:
```php
// Call private channel - for call signaling between users
Broadcast::channel('call.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

---

### Bug #2: FCM Notification Sent as `notification+data` Instead of Pure `data` Message

> [!CAUTION]
> **This is why the call screen doesn't wake up and doesn't show on Android when the app is killed/backgrounded.**

In [FcmService.php:48-51](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Services/FcmService.php#L48-L51):

```php
$message = CloudMessage::new()
    ->withToken($deviceToken)
    ->withNotification(Notification::create($title, $body))  // ⛔ THIS IS THE PROBLEM
    ->withData($stringData)
```

When you include `->withNotification(...)`, FCM treats this as a **notification message**. On Android:
- **Foreground:** Your `onMessage` handler fires, data payload accessible → works.
- **Background/killed:** Android's **system tray** handles the notification automatically. Your Flutter `onBackgroundMessage` handler **cannot trigger the full-screen incoming call UI** because the system already consumed the notification. The `data` payload is accessible but the app does NOT get priority execution to show a call screen.

For **VoIP/incoming call scenarios**, you MUST send a **data-only message** (no `notification` field). This forces the OS to deliver the payload to your app's background handler, where you can use packages like `flutter_callkit_incoming` or `flutter_local_notifications` with `fullScreenIntent` to wake the screen.

**Fix — FcmService.php:**
```php
public function sendCallNotification(string $deviceToken, array $data)
{
    if (!$this->messaging) {
        \Log::warning('Firebase Messaging not configured.');
        return false;
    }

    $stringData = array_map('strval', $data);

    try {
        // DATA-ONLY message — NO withNotification()
        $message = CloudMessage::new()
            ->withToken($deviceToken)
            ->withData($stringData)
            ->withAndroidConfig(
                AndroidConfig::fromArray([
                    'priority' => 'high',
                    'ttl'      => '30s',
                ])
            )
            ->withApnsConfig(
                ApnsConfig::fromArray([
                    'headers' => [
                        'apns-priority'  => '10',
                        'apns-push-type' => 'voip',  // Use VoIP push type on iOS
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                        ],
                    ],
                ])
            );

        $this->messaging->send($message);
        \Log::info('FCM call notification sent (data-only)', [
            'token_prefix' => substr($deviceToken, 0, 20),
        ]);
        return true;
    } catch (\Exception $e) {
        \Log::error('Firebase call notification error: ' . $e->getMessage());
        return false;
    }
}
```

Then in `CallController::initiate()`, call `sendCallNotification()` instead of `sendPushNotification()` for `incoming_call` messages.

---

### Bug #3: `call_type` Enum Mismatch — `voice` vs `audio`

> [!WARNING]
> The validation in CallController accepts `'voice'` but the database enum only allows `'audio'` or `'video'`.

In [CallController.php:110](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Http/Controllers/Api/CallController.php#L110):

```php
'type' => 'required|string|in:voice,video,audio'
```

The database migration [create_calls_table.php:19](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/database/migrations/2025_06_11_215126_create_calls_table.php#L19):

```php
$table->enum('call_type', ['audio', 'video']);
```

If the Flutter app sends `type: "voice"`, the call record creation will throw a MySQL error because `voice` is not in the enum. The call creation **silently fails** inside the try-catch, returning a 500 error.

**Fix:**
```php
// Either normalize the value:
$callType = $request->type === 'voice' ? 'audio' : $request->type;

// Or only accept the enum values:
'type' => 'required|string|in:audio,video'
```

---

### Bug #4: Both Users Get Uid `0` — Agora Cannot Distinguish Users

> [!WARNING]
> Both caller and receiver tokens are generated with `uid = 0`, which can cause Agora to treat both connections as the same user.

In [CallController.php:222-223](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Http/Controllers/Api/CallController.php#L222-L223):

```php
$callerToken = $this->agoraService->generateToken($channelName, 0);
$receiverToken = $this->agoraService->generateToken($channelName, 0);
```

When `uid = 0`, Agora auto-assigns a uid on join. But since you're generating **two separate tokens with the same uid**, both tokens are actually **identical and interchangeable**. While this may not cause a crash, Agora best practice is to assign unique uids per participant so the SDK can properly distinguish and connect streams.

**Fix:**
```php
$callerUid = Auth::id();              // e.g. 1
$receiverUid = $receiverId;           // e.g. 2
$callerToken = $this->agoraService->generateToken($channelName, $callerUid);
$receiverToken = $this->agoraService->generateToken($channelName, $receiverUid);
```

---

### Bug #5: Broadcast Events Use `ShouldBroadcast` (Queued) — But Queue Worker May Not Be Running

> [!WARNING]
> All call events implement `ShouldBroadcast` (queued via database queue), not `ShouldBroadcastNow`. If `php artisan queue:work` is NOT running, **zero WebSocket events will ever be sent.**

Your `.env` has:
```
QUEUE_CONNECTION=database
```

And all events use:
```php
class CallInitiated implements ShouldBroadcast  // Queued!
```

If you haven't started `php artisan queue:work`, **all broadcast events are stuck in the `jobs` database table and never delivered.**

**Fix (choose one):**
1. **Run the queue worker:** `php artisan queue:work --queue=default`
2. **Or change events to `ShouldBroadcastNow`** (processes synchronously during the HTTP request):

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CallInitiated implements ShouldBroadcastNow
```

For real-time call signaling, `ShouldBroadcastNow` is strongly recommended since call events are time-critical and should not be delayed by queue processing.

---

### Bug #6: `BROADCAST_DRIVER` Env Variable Not Set — Broadcasting Defaults to `null`

> [!CAUTION]
> The broadcasting config uses `env('BROADCAST_DRIVER', 'null')` but your `.env` sets `BROADCAST_CONNECTION=pusher`, not `BROADCAST_DRIVER`.

In [broadcasting.php:18](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/config/broadcasting.php#L18):
```php
'default' => env('BROADCAST_DRIVER', 'null'),
```

Your `.env` has:
```
BROADCAST_CONNECTION=pusher
```

The env variable name is **`BROADCAST_CONNECTION`** but the config reads **`BROADCAST_DRIVER`**. This means the broadcast driver defaults to `'null'` — **all broadcast events go nowhere!**

> [!NOTE]
> In Laravel 11+, the env variable was renamed from `BROADCAST_DRIVER` to `BROADCAST_CONNECTION`. Check which Laravel version you're on and ensure the config file matches.

**Fix — `.env`:**
```env
BROADCAST_DRIVER=pusher
```
Or update `config/broadcasting.php` to read:
```php
'default' => env('BROADCAST_CONNECTION', env('BROADCAST_DRIVER', 'null')),
```

---

### Bug #7: Missing `getStreamTokens` Method — Route References Non-Existent Method

In [api-only.php:214](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/routes/api-only.php#L214):
```php
Route::get('/{callId}/stream-tokens', [CallController::class, 'getStreamTokens']);
```

But `CallController` has **no `getStreamTokens` method**. The `api.php` routes use `getAgoraTokens` instead:
```php
Route::get('/{callId}/agora-tokens', [CallController::class, 'getAgoraTokens']);
```

If your Flutter app hits `/api/calls/{id}/stream-tokens`, it will get a 500 error, and User B won't be able to retrieve their Agora token to join the channel.

**Fix — Either:**
1. Add a `getStreamTokens` method that aliases `getAgoraTokens`, or
2. Update `api-only.php` to match `api.php`:
```php
Route::get('/{callId}/agora-tokens', [CallController::class, 'getAgoraTokens']);
```

---

### Bug #8: Missing FCM Token Update Route in `api-only.php`

The `api.php` route file has:
```php
Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
```

But `api-only.php` does **NOT** have this route. If your Flutter app connects via the `api-only` server, it **cannot register its FCM token**. Without a stored FCM token, no push notifications will ever be sent, and User B will never know a call is incoming.

**Fix — Add to `api-only.php` inside the `auth` prefix group:**
```php
Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
```

---

### Bug #9: `caller_avatar` in Broadcast Events References `->avatar` Instead of `->avatar_url`

In all event `broadcastWith()` methods (e.g., [CallInitiated.php:64](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Events/CallInitiated.php#L64)):

```php
'caller_avatar' => $this->caller->avatar,  // ⛔ wrong property name
```

The User model has the attribute `avatar_url`, not `avatar`. This means the avatar will always be `null` in the broadcast payload.

**Fix:**
```php
'caller_avatar' => $this->caller->avatar_url,
```

This should be fixed in ALL four event files: `CallInitiated`, `CallEnded`, `CallAccepted`, `CallRejected`.

---

### Bug #10: `end()` Returns Sensitive Data — FCM Tokens Leaked in Response

In [CallController.php:401-403](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Http/Controllers/Api/CallController.php#L401-L403):

```php
$call->load([
    'caller:id,name,phone_number,avatar_url,fcm_token,device_token',
    'receiver:id,name,phone_number,avatar_url,fcm_token,device_token'
]);
```

The response returns `$call` directly, which now includes `fcm_token` and `device_token` in the JSON. These are **security-sensitive credentials** — anyone who captures this response can send push notifications to those users.

**Fix:** Load the tokens for internal use, but hide them from the response:
```php
// After sending FCM, re-load without sensitive fields for the response
$call->load([
    'caller:id,name,phone_number,avatar_url',
    'receiver:id,name,phone_number,avatar_url'
]);
```

The same issue exists in the `decline()` method at lines 475-477.

---

### Bug #11: `getAgoraTokens` Uses `env()` Directly Instead of Settings Model

In [CallController.php:808](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Http/Controllers/Api/CallController.php#L808):

```php
'app_id' => env('AGORA_APP_ID'),
```

But in `initiate()` at [line 229](file:///c:/laragon/www/chat-app-backend/chatapp_back-end-api/app/Http/Controllers/Api/CallController.php#L229), the same value is fetched differently:

```php
'app_id' => \App\Models\Setting::get('agora_app_id', env('AGORA_APP_ID')),
```

This inconsistency means if the Agora App ID is configured via the Settings model (admin panel), the `getAgoraTokens` endpoint will return the **wrong app_id** from the env file, causing the Flutter app to fail when joining the channel.

**Fix:**
```php
'app_id' => \App\Models\Setting::get('agora_app_id', env('AGORA_APP_ID')),
```

---

## 📋 Priority Fix Order

| Priority | Bug | Impact | Effort |
|----------|-----|--------|--------|
| 🔴 P0 | #6 - `BROADCAST_DRIVER` not set | **ALL WebSocket events go nowhere** | 1 min |
| 🔴 P0 | #1 - Missing `call.{userId}` channel auth | **ALL call WebSocket events rejected** | 2 min |
| 🔴 P0 | #2 - FCM notification type wrong | **Call screen doesn't wake/show** | 15 min |
| 🔴 P0 | #5 - Queue worker not running | **Events never delivered** | 5 min |
| 🟠 P1 | #3 - `voice` vs `audio` enum mismatch | **Call creation can fail** | 2 min |
| 🟠 P1 | #7 - Missing `getStreamTokens` method | **Token retrieval 500 error** | 5 min |
| 🟠 P1 | #8 - Missing FCM route in api-only | **FCM token never saved** | 1 min |
| 🟡 P2 | #4 - Both users get uid 0 | **Agora may misroute streams** | 5 min |
| 🟡 P2 | #9 - Wrong avatar property name | **Avatar always null** | 2 min |
| 🟡 P2 | #10 - FCM tokens leaked in response | **Security vulnerability** | 2 min |
| 🟡 P2 | #11 - Inconsistent app_id source | **Token/app_id mismatch** | 1 min |

---

## 🔧 Quick Summary of What Causes Each Symptom

| Symptom | Root Causes |
|---------|------------|
| "Call screen not showing on User B" | Bug #2 (FCM notification type), Bug #6 (broadcast driver null), Bug #1 (channel not authorized), Bug #5 (queue not running) |
| "Keeps ringing after ending call" | Bug #6 (CallEnded broadcast goes nowhere), Bug #1 (channel not authorized), Bug #5 (queue not running) |
| "Not connecting between User A and B" | Bug #2 (receiver can't show UI to accept), Bug #4 (uid=0 for both), Bug #7 (token retrieval fails) |
| "Cannot answer or decline" | Bug #2 (incoming call UI never shown), Bug #1 (WebSocket events never arrive) |

---

> [!IMPORTANT]
> **Fix Bugs #6, #1, #2, and #5 first** — these four bugs together explain 100% of your symptoms. The remaining bugs are correctness and security issues that should be addressed after the core flow is working.

Would you like me to proceed with implementing all these fixes?
