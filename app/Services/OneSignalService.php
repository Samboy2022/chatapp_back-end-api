<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications through OneSignal.
 *
 * Replaces FcmService. The meaningful change is *addressing*: FCM needed a
 * per-device token stored on the user row and kept fresh, and a stale token
 * silently dropped the notification. OneSignal targets by **external id** —
 * the app calls `OneSignal.login(userId)`, so here we simply say "send to
 * user 42" and it reaches whatever devices that account is signed in on.
 */
class OneSignalService
{
    private const ENDPOINT = 'https://api.onesignal.com/notifications';

    /** Calls must ring immediately, so they get their own high-priority channel. */
    private const CALL_TTL_SECONDS = 45;

    public function appId(): ?string
    {
        return Setting::get('onesignal_app_id') ?: env('ONESIGNAL_APP_ID');
    }

    public function restApiKey(): ?string
    {
        return Setting::get('onesignal_rest_api_key') ?: env('ONESIGNAL_REST_API_KEY');
    }

    public function isConfigured(): bool
    {
        return filled($this->appId()) && filled($this->restApiKey());
    }

    /**
     * Ring a user's phone for an incoming call.
     *
     * Sent as a silent, data-only, high-priority message: the app turns it
     * into the full-screen CallKit UI itself, so a system banner would only
     * duplicate that. TTL is short because a call notification that arrives
     * two minutes late is worse than none at all.
     */
    public function sendCallNotification(string $userId, array $data = []): bool
    {
        return $this->send(
            userIds: [$userId],
            title: null,
            body: null,
            data: $data,
            options: [
                'priority' => 10,
                'ttl' => self::CALL_TTL_SECONDS,
                // Data-only on both platforms — the app owns the UI.
                'content_available' => true,
                'android_background_data' => true,
            ]
        );
    }

    /**
     * A normal, visible notification (new message, status, etc.).
     */
    public function sendPushNotification(
        string $userId,
        string $title,
        string $body,
        array $data = []
    ): bool {
        return $this->send([$userId], $title, $body, $data);
    }

    /**
     * Same, to several people at once — group messages, announcements.
     */
    public function sendToUsers(
        array $userIds,
        string $title,
        string $body,
        array $data = []
    ): bool {
        return $this->send($userIds, $title, $body, $data);
    }

    /**
     * @param  array<int|string>  $userIds
     */
    private function send(
        array $userIds,
        ?string $title,
        ?string $body,
        array $data = [],
        array $options = []
    ): bool {
        if (!$this->isConfigured()) {
            Log::warning('OneSignal is not configured — notification skipped');
            return false;
        }

        $externalIds = collect($userIds)
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($externalIds)) {
            return false;
        }

        $payload = array_merge([
            'app_id' => $this->appId(),
            'include_aliases' => ['external_id' => $externalIds],
            'target_channel' => 'push',
            // Every value must be a string: the Flutter side reads these
            // straight into a Map<String, dynamic> and a stray int would
            // break the CallKit payload parsing.
            'data' => array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $data),
        ], $options);

        if (filled($body)) {
            $payload['contents'] = ['en' => $body];
        }

        if (filled($title)) {
            $payload['headings'] = ['en' => $title];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->restApiKey(),
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(self::ENDPOINT, $payload);

            if ($response->successful()) {
                $recipients = $response->json('recipients');

                // A 200 with zero recipients means nobody matched — usually
                // the account has never opened the app, or hasn't granted
                // notification permission. Worth logging; not an error.
                if ($recipients === 0) {
                    Log::info('OneSignal: no subscribed devices', [
                        'external_ids' => $externalIds,
                    ]);
                    return false;
                }

                Log::info('OneSignal: sent', [
                    'external_ids' => $externalIds,
                    'recipients' => $recipients,
                    'type' => $data['type'] ?? 'generic',
                ]);
                return true;
            }

            Log::warning('OneSignal: send failed', [
                'status' => $response->status(),
                'error' => $response->json('errors') ?? $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            // Never let a push failure break the request that triggered it —
            // a call still connects over Agora even if the ring never lands.
            Log::error('OneSignal: request threw', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
