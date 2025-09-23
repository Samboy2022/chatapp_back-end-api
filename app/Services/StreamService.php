<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StreamService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl = 'https://stream-io-api.com/api/2.0';

    public function __construct()
    {
        $this->apiKey = env('STREAM_API_KEY');
        $this->apiSecret = env('STREAM_API_SECRET');
    }

    /**
     * Create a Stream user token for video calls
     */
    public function createUserToken($userId, $exp = null)
    {
        try {
            // Set default expiration to 24 hours if not provided
            if ($exp === null) {
                $exp = now()->addHours(24)->timestamp;
            }

            // Create token payload
            $payload = [
                'user_id' => $userId,
                'exp' => $exp,
                'iat' => now()->timestamp,
                'iss' => 'laravel-app',
                'sub' => 'user/' . $userId,
                'scope' => 'video'
            ];

            // Create JWT token (simplified version)
            $token = $this->generateJWT($payload);

            Log::info('Stream token generated', [
                'user_id' => $userId,
                'exp' => $exp
            ]);

            return $token;

        } catch (\Exception $e) {
            Log::error('Failed to create Stream token', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Generate a simple JWT token (for demo purposes)
     * In production, use a proper JWT library like firebase/php-jwt
     */
    private function generateJWT($payload)
    {
        // Header
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

        // Payload
        $payload = json_encode($payload);

        // Encode
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Signature (simplified - use HMAC with API secret)
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->apiSecret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Validate Stream API credentials
     */
    public function validateCredentials()
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            return [
                'valid' => false,
                'message' => 'Stream API credentials not configured'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Stream API credentials configured'
        ];
    }

    /**
     * Get Stream configuration for frontend
     */
    public function getConfig()
    {
        return [
            'api_key' => $this->apiKey,
            'token_validity_hours' => 24,
            'features' => [
                'video_calling' => true,
                'screen_sharing' => true,
                'recording' => false
            ]
        ];
    }
}