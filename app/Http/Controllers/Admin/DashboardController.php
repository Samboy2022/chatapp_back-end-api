<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Status;
use App\Models\Call;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => User::count(),
            'active_users_today' => User::where('last_seen_at', '>=', Carbon::today())->count(),
            'total_chats' => Chat::count(),
            'private_chats' => Chat::where('type', 'private')->count(),
            'group_chats' => Chat::where('type', 'group')->count(),
            'total_messages' => Message::count(),
            'messages_today' => Message::whereDate('created_at', Carbon::today())->count(),
            'total_status_updates' => Status::count(),
            'active_statuses' => Status::where('expires_at', '>', Carbon::now())->count(),
            'total_calls' => Call::count(),
            'calls_today' => Call::whereDate('created_at', Carbon::today())->count(),
            'total_contacts' => Contact::count(),
            'blocked_contacts' => Contact::where('is_blocked', true)->count()
        ];

        // Get recent activities (limited to 5 items each)
        $recent_users = User::latest()->take(5)->get();
        $recent_messages = Message::with(['sender', 'chat'])
            ->latest()
            ->take(5)
            ->get();
        $recent_chats = Chat::with(['participants'])
            ->latest()
            ->take(5)
            ->get();

        // Get user growth chart data (last 30 days)
        $user_growth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Get message activity chart data (last 7 days)
        $message_activity = Message::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'recent_users', 
            'recent_messages', 
            'recent_chats',
            'user_growth',
            'message_activity'
        ));
    }

    public function systemHealth()
    {
        $health = [
            'database_status' => $this->checkDatabaseConnection(),
            'storage_usage' => $this->getStorageUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'active_connections' => $this->getActiveConnections(),
            'recent_errors' => $this->getRecentErrors()
        ];

        return view('admin.system-health', compact('health'));
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function getStorageUsage()
    {
        $disk = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        $used = $total - $disk;
        
        return [
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($disk),
            'total' => $this->formatBytes($total),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    private function getMemoryUsage()
    {
        return [
            'current' => $this->formatBytes(memory_get_usage()),
            'peak' => $this->formatBytes(memory_get_peak_usage()),
            'limit' => ini_get('memory_limit')
        ];
    }

    private function getActiveConnections()
    {
        // This would typically connect to your real-time service (Pusher, etc.)
        return [
            'websocket_connections' => 0, // Placeholder
            'api_active_sessions' => User::where('last_seen_at', '>=', Carbon::now()->subMinutes(5))->count()
        ];
    }

    private function getRecentErrors()
    {
        // This would typically read from your log files
        return [
            'total_errors_today' => 0, // Placeholder
            'critical_errors' => 0,
            'warnings' => 0
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
