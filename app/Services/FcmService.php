<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = env('FIREBASE_CREDENTIALS');
        if (!empty($credentialsPath) && file_exists($credentialsPath)) {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * Send a push notification to a specific device.
     */
    public function sendPushNotification(string $deviceToken, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            \Log::warning('Firebase Messaging not configured. Skipping push notification.');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase sending error: ' . $e->getMessage());
            return false;
        }
    }
}
