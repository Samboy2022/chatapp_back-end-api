<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * Get API health status
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API is healthy',
            'data' => [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0',
                'environment' => app()->environment()
            ]
        ]);
    }

    /**
     * Get broadcast health status
     */
    public function broadcast(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Broadcast system check',
            'data' => [
                'broadcast_driver' => config('broadcasting.default'),
                'pusher_configured' => !empty(config('broadcasting.connections.pusher')),
                'reverb_configured' => !empty(config('broadcasting.connections.reverb')),
                'timestamp' => now()->toISOString()
            ]
        ]);
    }
}