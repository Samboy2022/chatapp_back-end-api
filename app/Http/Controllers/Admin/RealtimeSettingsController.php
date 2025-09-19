<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealtimeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class RealtimeSettingsController extends Controller
{
    /**
     * Display realtime settings page
     */
    public function index()
    {
        try {
            $settings = RealtimeSetting::current();
            $connectionStatus = $this->getConnectionStatus();
            
            return view('admin.realtime-settings.index', compact('settings', 'connectionStatus'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load realtime settings: ' . $e->getMessage());
            
            return view('admin.realtime-settings.index', [
                'settings' => new RealtimeSetting(),
                'connectionStatus' => [
                    'success' => false,
                    'message' => 'Failed to load settings: ' . $e->getMessage(),
                    'driver' => 'log'
                ]
            ]);
        }
    }

    /**
     * Update realtime settings
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:enabled,disabled',
                'driver' => 'required|in:pusher,reverb',
                'pusher_app_id' => 'nullable|string|max:255',
                'pusher_key' => 'nullable|string|max:255',
                'pusher_secret' => 'nullable|string|max:255',
                'pusher_cluster' => 'nullable|string|max:50',
                'reverb_app_id' => 'nullable|string|max:255',
                'reverb_key' => 'nullable|string|max:255',
                'reverb_secret' => 'nullable|string|max:255',
                'reverb_host' => 'nullable|string|max:255',
                'reverb_port' => 'nullable|integer|min:1|max:65535',
                'reverb_scheme' => 'nullable|in:http,https',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the validation errors.');
            }

            // Get current settings or create new
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

            // Clear cache and apply new settings
            Cache::forget('realtime_settings');
            RealtimeSetting::applyToConfig();

            Log::info('Realtime settings updated successfully', [
                'status' => $settings->status,
                'driver' => $settings->driver,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Realtime settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update realtime settings: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get current connection status
     */
    public function status()
    {
        try {
            $status = $this->getConnectionStatus();
            
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test connection with current settings
     */
    public function testConnection()
    {
        try {
            $result = RealtimeSetting::testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'driver' => $result['driver']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function reset()
    {
        try {
            $settings = RealtimeSetting::first();
            if ($settings) {
                $settings->update([
                    'status' => 'enabled',
                    'driver' => 'pusher',
                    'pusher_app_id' => env('PUSHER_APP_ID', '2012149'),
                    'pusher_key' => env('PUSHER_APP_KEY', 'b3652bc3e7cddc5d6f80'),
                    'pusher_secret' => env('PUSHER_APP_SECRET', 'a58bf3bdccfb58ded089'),
                    'pusher_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                    'reverb_app_id' => env('REVERB_APP_ID', 'chatapp'),
                    'reverb_key' => env('REVERB_APP_KEY', 'chatapp-key'),
                    'reverb_secret' => env('REVERB_APP_SECRET', 'chatapp-secret'),
                    'reverb_host' => env('REVERB_HOST', '127.0.0.1'),
                    'reverb_port' => env('REVERB_PORT', 8080),
                    'reverb_scheme' => env('REVERB_SCHEME', 'http'),
                ]);
            }

            // Clear cache and apply settings
            Cache::forget('realtime_settings');
            RealtimeSetting::applyToConfig();

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get connection status
     */
    private function getConnectionStatus()
    {
        try {
            $settings = RealtimeSetting::current();
            $testResult = RealtimeSetting::testConnection();
            
            return [
                'enabled' => $settings->status === 'enabled',
                'driver' => $settings->driver,
                'connected' => $testResult['success'],
                'message' => $testResult['message'],
                'settings' => [
                    'status' => $settings->status,
                    'driver' => $settings->driver,
                    'pusher_configured' => !empty($settings->pusher_key) && !empty($settings->pusher_secret) && !empty($settings->pusher_app_id),
                    'reverb_configured' => !empty($settings->reverb_key) && !empty($settings->reverb_secret) && !empty($settings->reverb_app_id),
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'enabled' => false,
                'driver' => 'log',
                'connected' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'settings' => [
                    'status' => 'disabled',
                    'driver' => 'log',
                    'pusher_configured' => false,
                    'reverb_configured' => false,
                ]
            ];
        }
    }
}
