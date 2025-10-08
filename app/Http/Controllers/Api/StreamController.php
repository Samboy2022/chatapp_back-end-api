<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StreamService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StreamController extends Controller
{
    protected $streamService;

    public function __construct(StreamService $streamService)
    {
        $this->streamService = $streamService;
    }

    /**
     * Generate Stream video token for authenticated user
     */
    public function generateToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'call_id' => 'sometimes|string',
                'room_id' => 'sometimes|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();

            // Generate token with 24-hour expiration
            $token = $this->streamService->createUserToken($userId);

            // Get Stream configuration
            $config = $this->streamService->getConfig();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'api_key' => $config['api_key'],
                    'user_id' => (string) $userId,  // ✅ FIXED: Cast to string for Stream SDK compatibility
                    'expires_at' => now()->addHours(24)->toISOString(),
                    'call_id' => $request->call_id,
                    'room_id' => $request->room_id
                ],
                'message' => 'Stream video token generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating Stream token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Stream configuration for frontend
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = $this->streamService->getConfig();

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Stream configuration retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving Stream configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate Stream credentials
     */
    public function validateCredentials(): JsonResponse
    {
        try {
            $validation = $this->streamService->validateCredentials();

            return response()->json([
                'success' => $validation['valid'],
                'data' => [
                    'credentials_configured' => $validation['valid']
                ],
                'message' => $validation['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating Stream credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate token for specific user (admin endpoint)
     */
    public function generateTokenForUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'call_id' => 'sometimes|string',
                'room_id' => 'sometimes|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate token for specified user
            $token = $this->streamService->createUserToken($request->user_id);

            // Get Stream configuration
            $config = $this->streamService->getConfig();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'api_key' => $config['api_key'],
                    'user_id' => (string) $request->user_id,  // ✅ FIXED: Cast to string for Stream SDK compatibility
                    'expires_at' => now()->addHours(24)->toISOString(),
                    'call_id' => $request->call_id,
                    'room_id' => $request->room_id
                ],
                'message' => 'Stream video token generated for user successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating Stream token for user: ' . $e->getMessage()
            ], 500);
        }
    }
}