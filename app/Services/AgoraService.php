<?php

namespace App\Services;

use App\Models\Setting;
use TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder;

class AgoraService
{
    public function appId(): ?string
    {
        return Setting::get('agora_app_id', config('services.agora.app_id')) ?: null;
    }

    /**
     * Whether calls can be placed at all: both the App ID and the App
     * Certificate are needed to mint tokens.
     */
    public function isConfigured(): bool
    {
        return $this->appId() !== null && $this->certificate() !== null;
    }

    /**
     * Generate an Agora RTC token for [$uid] in [$channelName].
     *
     * The uid must be the exact integer the app joins with, or Agora rejects
     * the join.
     */
    public function generateToken(string $channelName, int $uid, int $role = RtcTokenBuilder::RolePublisher, ?int $expireTimeInSeconds = null): string
    {
        $appId = $this->appId();
        $appCertificate = $this->certificate();

        if ($appId === null || $appCertificate === null) {
            \Log::error('Agora App ID or Certificate is missing.');
            throw new \Exception('Agora configuration is missing. Cannot generate token.');
        }

        $expireTimeInSeconds = $expireTimeInSeconds ?? (int) config('services.agora.token_expiry', 3600);
        $privilegeExpiredTs = now()->getTimestamp() + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }

    private function certificate(): ?string
    {
        return Setting::get('agora_app_certificate', config('services.agora.app_certificate')) ?: null;
    }
}
