<?php

namespace App\Services;

use Taylanunutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    /**
     * Generate an Agora RTC Token.
     */
    public function generateToken(string $channelName, int $uid, int $role = RtcTokenBuilder::RoleAttendee, ?int $expireTimeInSeconds = null): string
    {
        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        
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
