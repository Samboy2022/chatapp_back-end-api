<?php

namespace App\Services;

use App\Helpers\PhoneNumber;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS delivery through Termii (https://developers.termii.com).
 *
 * We only use Termii's plain messaging endpoint and generate/verify the OTP
 * ourselves (see OtpService). That keeps one verification path shared with
 * email codes, and means a Termii outage can't lock out a user who already
 * has a valid code in hand.
 *
 * Channel note: most Nigerian mobile numbers sit on the Do-Not-Disturb list,
 * which silently drops `generic` traffic. `dnd` is the default here for that
 * reason — it costs more but actually arrives.
 */
class TermiiService
{
    private const SEND_PATH = '/api/sms/send';
    private const BALANCE_PATH = '/api/get-balance';
    private const TIMEOUT_SECONDS = 20;

    public function apiKey(): ?string
    {
        return Setting::get('termii_api_key') ?: env('TERMII_API_KEY');
    }

    public function senderId(): string
    {
        return Setting::get('termii_sender_id') ?: (env('TERMII_SENDER_ID') ?: 'N-Alert');
    }

    public function channel(): string
    {
        return Setting::get('termii_channel') ?: 'dnd';
    }

    public function baseUrl(): string
    {
        return rtrim(Setting::get('termii_base_url') ?: 'https://api.ng.termii.com', '/');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey());
    }

    /**
     * Send one SMS.
     *
     * Returns a result array rather than a bare bool so callers can surface a
     * real reason to the admin ("insufficient balance", "sender id not
     * approved") instead of a generic failure.
     *
     * @return array{success: bool, message: string, message_id?: string, balance?: float}
     */
    public function send(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Termii is not configured. Add the API key under Settings → SMS.',
            ];
        }

        $recipient = PhoneNumber::forSms($to);

        if ($recipient === '') {
            return ['success' => false, 'message' => 'Invalid phone number.'];
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->acceptJson()
                ->post($this->baseUrl() . self::SEND_PATH, [
                    'api_key' => $this->apiKey(),
                    'to' => $recipient,
                    'from' => $this->senderId(),
                    'sms' => $message,
                    'type' => 'plain',
                    'channel' => $this->channel(),
                ]);

            $body = $response->json() ?? [];

            // Termii answers 200 with a `code` field; anything other than "ok"
            // is a failure even though the HTTP status looks fine.
            $ok = $response->successful()
                && (($body['code'] ?? null) === 'ok' || ($body['message'] ?? null) === 'Successfully Sent');

            if (!$ok) {
                Log::warning('Termii SMS send failed', [
                    'to' => $recipient,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return [
                    'success' => false,
                    'message' => $body['message'] ?? 'SMS gateway rejected the message.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Message sent',
                'message_id' => (string) ($body['message_id'] ?? $body['message_id_str'] ?? ''),
                'balance' => isset($body['balance']) ? (float) $body['balance'] : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Termii SMS send threw', [
                'to' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Could not reach the SMS gateway. Please try again.',
            ];
        }
    }

    /**
     * Remaining Termii credit — shown on the admin settings page so nobody
     * discovers an empty wallet by way of users failing to log in.
     *
     * @return array{success: bool, message: string, balance?: float, currency?: string}
     */
    public function balance(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Termii is not configured.'];
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->acceptJson()
                ->get($this->baseUrl() . self::BALANCE_PATH, [
                    'api_key' => $this->apiKey(),
                ]);

            $body = $response->json() ?? [];

            if (!$response->successful() || !isset($body['balance'])) {
                return [
                    'success' => false,
                    'message' => $body['message'] ?? 'Could not read balance.',
                ];
            }

            return [
                'success' => true,
                'message' => 'OK',
                'balance' => (float) $body['balance'],
                'currency' => (string) ($body['currency'] ?? 'NGN'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Could not reach the SMS gateway.',
            ];
        }
    }
}
