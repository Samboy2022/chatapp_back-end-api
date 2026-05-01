<?php

namespace App\Services;

use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    /**
     * Generate an Agora RTC Token.
     */
    public function generateToken(string $channelName, int $uid, int $role = RtcTokenBuilder::RolePublisher, ?int $expireTimeInSeconds = null): string
    {
        $appId = \App\Models\Setting::get('agora_app_id', env('AGORA_APP_ID'));
        $appCertificate = \App\Models\Setting::get('agora_app_certificate', env('AGORA_APP_CERTIFICATE'));
        
        $expireTimeInSeconds = $expireTimeInSeconds ?? (int) env('AGORA_TOKEN_EXPIRY', 3600);
        $currentTimestamp = now()->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        if (empty($appId) || empty($appCertificate)) {
            \Log::error('Agora App ID or Certificate is missing.');
            throw new \Exception('Agora configuration is missing. Cannot generate token.');
        }

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }
}
