<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ApiTesterController extends Controller
{
    /**
     * Show the API tester dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'api_url' => config('app.url'),
            'stream_api_key' => config('services.stream.key', env('STREAM_API_KEY')),
            'stream_configured' => !empty(env('STREAM_API_KEY')) && !empty(env('STREAM_API_SECRET')),
        ];

        return view('api-tester', compact('stats'));
    }

    /**
     * Get system status
     */
    public function status()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'disconnected';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'api_status' => 'online',
                'database_status' => $dbStatus,
                'stream_configured' => !empty(env('STREAM_API_KEY')),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }
}
