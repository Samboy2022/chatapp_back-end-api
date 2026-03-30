<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RealtimeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BroadcastSettingsController extends Controller
{
    /**
     * Get current broadcast settings for mobile/frontend apps
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $settings = RealtimeSetting::current();
            $config = RealtimeSetting::getFrontendConfig();
            $testResult = RealtimeSetting::testConnection();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'enabled' => $settings->status === 'enabled',
                    'driver' => $settings->driver,
                    'config' => $config,
                    'connection_status' => [
                        'connected' => $testResult['success'],
                        'message' => $testResult['message']
                    ],
                    'call_signaling' => [
                        'enabled' => $settings->status === 'enabled',
                        'channel_pattern' => 'call.{userId}',
                        'events' => [
                            'CallInitiated',
                            'CallAccepted',
                            'CallEnded',
                            'CallRejected'
                        ],
                        'auth_required' => true,
                    ],
                    'last_updated' => $settings->updated_at->toISOString(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get broadcast settings for API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve broadcast settings',
                'error' => $e->getMessage(),
                'data' => [
                    'enabled' => false,
                    'driver' => 'log',
                    'config' => [
                        'enabled' => false,
                        'driver' => 'log',
                        'message' => 'Broadcasting is disabled due to configuration error'
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Update broadcast settings (Admin only)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:enabled,disabled',
                'driver' => 'required_if:status,enabled|in:pusher,reverb',
                'pusher_app_id' => 'required_if:driver,pusher|nullable|string|max:255',
                'pusher_key' => 'required_if:driver,pusher|nullable|string|max:255',
                'pusher_secret' => 'required_if:driver,pusher|nullable|string|max:255',
                'pusher_cluster' => 'nullable|string|max:50',
                'reverb_app_id' => 'required_if:driver,reverb|nullable|string|max:255',
                'reverb_key' => 'required_if:driver,reverb|nullable|string|max:255',
                'reverb_secret' => 'required_if:driver,reverb|nullable|string|max:255',
                'reverb_host' => 'nullable|string|max:255',
                'reverb_port' => 'nullable|integer|min:1|max:65535',
                'reverb_scheme' => 'nullable|in:http,https',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get or create settings
            $settings = RealtimeSetting::first();
            if (!$settings) {
                $settings = new RealtimeSetting();
            }

            // Update settings
            $settings->fill($request->only([
                'status',
                'driver',
                'pusher_app_id',
                'pusher_key',
                'pusher_secret',
                'pusher_cluster',
                'reverb_app_id',
                'reverb_key',
                'reverb_secret',
                'reverb_host',
                'reverb_port',
                'reverb_scheme',
            ]));

            $settings->save();

            // Apply new configuration
            RealtimeSetting::applyToConfig();

            // Test new configuration
            $testResult = RealtimeSetting::testConnection();

            Log::info('Broadcast settings updated via API', [
                'status' => $settings->status,
                'driver' => $settings->driver,
                'connection_test' => $testResult['success'],
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Broadcast settings updated successfully',
                'data' => [
                    'enabled' => $settings->status === 'enabled',
                    'driver' => $settings->driver,
                    'config' => RealtimeSetting::getFrontendConfig(),
                    'connection_status' => [
                        'connected' => $testResult['success'],
                        'message' => $testResult['message']
                    ],
                    'updated_at' => $settings->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update broadcast settings via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update broadcast settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get connection info for WebSocket clients
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectionInfo()
    {
        try {
            $config = RealtimeSetting::getFrontendConfig();
            
            if (!$config['enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Broadcasting is disabled',
                    'data' => [
                        'enabled' => false,
                        'driver' => 'log'
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get connection info: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get connection information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test current broadcast connection
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            $result = RealtimeSetting::testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'driver' => $result['driver'],
                    'connected' => $result['success'],
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
                'data' => [
                    'connected' => false,
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Get broadcast status (lightweight endpoint for frequent polling)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        try {
            $settings = RealtimeSetting::current();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'enabled' => $settings->status === 'enabled',
                    'driver' => $settings->driver,
                    'last_updated' => $settings->updated_at->timestamp
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status',
                'data' => [
                    'enabled' => false,
                    'driver' => 'log'
                ]
            ], 500);
        }
    }

    /**
     * Health check endpoint
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        try {
            $settings = RealtimeSetting::current();
            $testResult = RealtimeSetting::testConnection();
            
            $health = [
                'status' => $testResult['success'] ? 'healthy' : 'unhealthy',
                'broadcast_enabled' => $settings->status === 'enabled',
                'driver' => $settings->driver,
                'connection' => $testResult['success'],
                'message' => $testResult['message'],
                'timestamp' => now()->toISOString()
            ];
            
            $httpStatus = $testResult['success'] ? 200 : 503;
            
            return response()->json([
                'success' => true,
                'data' => $health
            ], $httpStatus);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Get call signaling configuration for mobile apps
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callSignalingConfig()
    {
        try {
            $settings = RealtimeSetting::current();
            $config = RealtimeSetting::getFrontendConfig();

            return response()->json([
                'success' => true,
                'data' => [
                    'enabled' => $settings->status === 'enabled',
                    'driver' => $settings->driver,
                    'websocket_config' => $config,
                    'call_channels' => [
                        'private_pattern' => 'call.{userId}',
                        'description' => 'Subscribe to call.{your_user_id} to receive call events',
                        'auth_endpoint' => $config['auth_endpoint'] ?? null,
                        'requires_authentication' => true,
                    ],
                    'call_events' => [
                        'CallInitiated' => [
                            'description' => 'Fired when someone initiates a call to you',
                            'data_structure' => [
                                'event_type' => 'call_initiated',
                                'call_id' => 'unique_call_identifier',
                                'caller_id' => 'caller_user_id',
                                'recipient_id' => 'recipient_user_id',
                                'caller_name' => 'caller_display_name',
                                'caller_avatar' => 'caller_avatar_url',
                                'call_type' => 'voice|video',
                                'timestamp' => 'ISO_timestamp',
                                'metadata' => 'additional_call_data'
                            ]
                        ],
                        'CallAccepted' => [
                            'description' => 'Fired when a call is accepted by the recipient',
                            'data_structure' => [
                                'event_type' => 'call_accepted',
                                'call_id' => 'unique_call_identifier',
                                'caller_id' => 'caller_user_id',
                                'recipient_id' => 'recipient_user_id',
                                'timestamp' => 'ISO_timestamp',
                                'metadata' => 'call_session_data'
                            ]
                        ],
                        'CallEnded' => [
                            'description' => 'Fired when a call is ended by either party',
                            'data_structure' => [
                                'event_type' => 'call_ended',
                                'call_id' => 'unique_call_identifier',
                                'caller_id' => 'caller_user_id',
                                'recipient_id' => 'recipient_user_id',
                                'timestamp' => 'ISO_timestamp',
                                'metadata' => [
                                    'duration' => 'call_duration_seconds',
                                    'ended_by' => 'user_who_ended_call'
                                ]
                            ]
                        ],
                        'CallRejected' => [
                            'description' => 'Fired when a call is rejected by the recipient',
                            'data_structure' => [
                                'event_type' => 'call_rejected',
                                'call_id' => 'unique_call_identifier',
                                'caller_id' => 'caller_user_id',
                                'recipient_id' => 'recipient_user_id',
                                'timestamp' => 'ISO_timestamp'
                            ]
                        ]
                    ],
                    'integration_guide' => [
                        'step_1' => 'Check if call signaling is enabled via this endpoint',
                        'step_2' => 'Initialize WebSocket connection using websocket_config',
                        'step_3' => 'Subscribe to private channel: call.{your_user_id}',
                        'step_4' => 'Listen for CallInitiated, CallAccepted, CallEnded, CallRejected events',
                        'step_5' => 'Handle events to trigger WebRTC session establishment'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get call signaling config: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve call signaling configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
