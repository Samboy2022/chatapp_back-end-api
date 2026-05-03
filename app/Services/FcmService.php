<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = \App\Models\Setting::get('firebase_credentials', env('FIREBASE_CREDENTIALS'));

        // If the path is relative (doesn't start with / or C:\), make it absolute using base_path()
        if (!empty($credentialsPath) && !preg_match('/^([a-zA-Z]:\\\\|\/)/', $credentialsPath)) {
            $credentialsPath = base_path($credentialsPath);
        }

        if (!empty($credentialsPath) && file_exists($credentialsPath)) {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * Send a DATA-ONLY push for call signaling (incoming_call, call_ended, call_declined).
     *
     * Data-only messages are NOT intercepted by the Android OS notification tray.
     * They ALWAYS reach the app's onBackgroundMessage handler even when terminated,
     * which allows flutter_callkit_incoming to show the native call UI.
     *
     * All data values MUST be strings (FCM requirement).
     */
    public function sendCallNotification(string $deviceToken, array $data = []): bool
    {
        if (!$this->messaging) {
            \Log::warning('Firebase Messaging not configured. Skipping call notification.');
            return false;
        }

        // FCM requires all data values to be strings
        $stringData = array_map('strval', $data);

        try {
            $message = CloudMessage::new()
                ->withToken($deviceToken)
                // ✅ DATA-ONLY — no withNotification() so Android routes to background handler
                ->withData($stringData)
                ->withAndroidConfig(
                    AndroidConfig::fromArray([
                        'priority' => 'high',          // wake the device immediately
                        'ttl'      => '30s',            // discard after 30 s if undelivered
                    ])
                )
                ->withApnsConfig(
                    ApnsConfig::fromArray([
                        'headers' => [
                            'apns-priority'  => '10',   // high priority on iOS
                            'apns-push-type' => 'voip', // VoIP push for CallKit on iOS
                        ],
                        'payload' => [
                            'aps' => [
                                'content-available' => 1, // wake background app on iOS
                            ],
                        ],
                    ])
                );

            $this->messaging->send($message);
            \Log::info('FCM call notification sent', [
                'token_prefix' => substr($deviceToken, 0, 20),
                'type'         => $data['type'] ?? 'unknown',
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase call notification error: ' . $e->getMessage(), [
                'token_prefix' => substr($deviceToken, 0, 20),
                'type'         => $data['type'] ?? 'unknown',
            ]);
            return false;
        }
    }

    /**
     * Send a standard push notification WITH a visible notification bar entry.
     * Use this for chat messages, status updates, etc. — NOT for calls.
     *
     * All data values MUST be strings (FCM requirement).
     */
    public function sendPushNotification(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            \Log::warning('Firebase Messaging not configured. Skipping push notification.');
            return false;
        }

        // FCM requires all data values to be strings
        $stringData = array_map('strval', $data);

        try {
            $message = CloudMessage::new()
                ->withToken($deviceToken)
                ->withNotification(Notification::create($title, $body))
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
                            'apns-push-type' => 'alert',
                        ],
                        'payload' => [
                            'aps' => [
                                'content-available' => 1,
                                'sound'             => 'default',
                            ],
                        ],
                    ])
                );

            $this->messaging->send($message);
            \Log::info('FCM notification sent', ['token_prefix' => substr($deviceToken, 0, 20), 'title' => $title]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase sending error: ' . $e->getMessage(), [
                'token_prefix' => substr($deviceToken, 0, 20),
                'title'        => $title,
            ]);
            return false;
        }
    }
}
