<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Chat;
use App\Models\Call;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        // Middleware is applied at the route level
    }

    public function index()
    {
        // Quick stats for dashboard
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count(),
            'weekly_users' => User::where('created_at', '>=', Carbon::now()->subWeek())->count(),
            'online_users' => User::where('last_seen_at', '>=', Carbon::now()->subMinutes(5))->count(),
            'total_messages' => Message::count(),
            'today_messages' => Message::whereDate('created_at', Carbon::today())->count(),
            'total_chats' => Chat::count(),
            'group_chats' => Chat::where('type', 'group')->count(),
            'total_calls' => Call::count(),
            'successful_calls' => Call::where('status', 'ended')->count(),
            'total_statuses' => Status::count(),
            'active_statuses' => Status::where('expires_at', '>', Carbon::now())->count(),
        ];

        // User status distribution for charts
        $userStatusDistribution = [
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return view('admin.reports.index', compact('stats', 'userStatusDistribution'));
    }

    public function users()
    {
        // User registration trends
        $userRegistrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // User activity by device
        $deviceStats = User::selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Top active users by message count
        $topUsers = User::withCount('sentMessages')
            ->orderBy('sent_messages_count', 'desc')
            ->limit(10)
            ->get();

        // User status distribution
        $userStatuses = [
            'online' => User::where('is_online', true)->count(),
            'offline' => User::where('is_online', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        return view('admin.reports.users', compact('userRegistrations', 'deviceStats', 'topUsers', 'userStatuses'));
    }

    public function activity()
    {
        // Daily message activity
        $messageActivity = Message::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Message types distribution
        $messageTypes = Message::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        // Hourly activity pattern
        $hourlyActivity = Message::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Chat activity
        $chatActivity = [
            'most_active_chats' => Chat::withCount('messages')
                ->orderBy('messages_count', 'desc')
                ->limit(10)
                ->get(),
            'group_vs_private' => [
                'group' => Chat::where('type', 'group')->count(),
                'private' => Chat::where('type', 'private')->count(),
            ]
        ];

        // Call statistics
        $callStats = [
            'total_calls' => Call::count(),
            'video_calls' => Call::where('type', 'video')->count(),
            'voice_calls' => Call::where('type', 'voice')->count(),
            'average_duration' => Call::where('status', 'ended')->avg('duration') ?? 0,
        ];

        return view('admin.reports.activity', compact('messageActivity', 'messageTypes', 'hourlyActivity', 'chatActivity', 'callStats'));
    }

    public function performance()
    {
        // System performance metrics
        $performance = [
            'database_size' => $this->getDatabaseSize(),
            'storage_usage' => $this->getStorageUsage(),
            'average_response_time' => $this->getAverageResponseTime(),
            'uptime' => $this->getSystemUptime(),
        ];

        // Error logs (last 30 days)
        $errorLogs = $this->getErrorLogs();

        // Peak usage times
        $peakUsage = Message::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Server health metrics
        $serverHealth = [
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'disk_space' => $this->getDiskSpace(),
        ];

        return view('admin.reports.performance', compact('performance', 'errorLogs', 'peakUsage', 'serverHealth'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'users');
        $format = $request->get('format', 'csv');

        switch ($type) {
            case 'users':
                return $this->exportUsers($format);
            case 'messages':
                return $this->exportMessages($format);
            case 'chats':
                return $this->exportChats($format);
            case 'calls':
                return $this->exportCalls($format);
            default:
                return redirect()->back()->with('error', 'Invalid export type');
        }
    }

    private function exportUsers($format)
    {
        $users = User::with(['sentMessages', 'receivedCalls'])
            ->get()
            ->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Phone' => $user->phone_number,
                    'Status' => $user->is_active ? 'Active' : 'Inactive',
                    'Verified' => $user->email_verified_at ? 'Yes' : 'No',
                    'Messages Sent' => $user->sent_messages_count ?? 0,
                    'Calls Made' => $user->received_calls_count ?? 0,
                    'Joined Date' => $user->created_at->format('Y-m-d H:i:s'),
                    'Last Seen' => $user->last_seen_at ? $user->last_seen_at->format('Y-m-d H:i:s') : 'Never',
                ];
            });

        return $this->downloadCsv($users, 'users_report_' . date('Y-m-d'));
    }

    private function exportMessages($format)
    {
        $messages = Message::with(['sender', 'chat'])
            ->get()
            ->map(function ($message) {
                return [
                    'ID' => $message->id,
                    'Sender' => $message->sender->name ?? 'Unknown',
                    'Chat' => $message->chat->name ?? "Chat #{$message->chat_id}",
                    'Type' => ucfirst($message->type),
                    'Content' => Str::limit($message->content, 50),
                    'Status' => ucfirst($message->status),
                    'Sent At' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return $this->downloadCsv($messages, 'messages_report_' . date('Y-m-d'));
    }

    private function exportCalls($format)
    {
        $calls = Call::with(['caller', 'receiver'])
            ->get()
            ->map(function ($call) {
                return [
                    'ID' => $call->id,
                    'Caller' => $call->caller->name ?? 'Unknown',
                    'Receiver' => $call->receiver->name ?? 'Unknown',
                    'Type' => ucfirst($call->type),
                    'Status' => ucfirst($call->status),
                    'Duration' => $call->duration ? gmdate('H:i:s', $call->duration) : '00:00:00',
                    'Started At' => $call->started_at ? $call->started_at->format('Y-m-d H:i:s') : 'N/A',
                    'Ended At' => $call->ended_at ? $call->ended_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            });

        return $this->downloadCsv($calls, 'calls_report_' . date('Y-m-d'));
    }

    private function downloadCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Helper methods for system metrics
    private function getDatabaseSize()
    {
        try {
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema = DATABASE()")[0];
            return $size->{'DB Size in MB'} . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getStorageUsage()
    {
        try {
            $bytes = disk_free_space(storage_path());
            return round($bytes / 1024 / 1024 / 1024, 2) . ' GB free';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getAverageResponseTime()
    {
        // This would typically come from application monitoring
        return '150ms'; // Placeholder
    }

    private function getSystemUptime()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return 'N/A (Windows)';
            }
            $uptime = shell_exec('uptime -p');
            return trim($uptime) ?: 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getErrorLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logs = file_get_contents($logPath);
                $errorCount = substr_count($logs, '[ERROR]');
                return $errorCount;
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMemoryUsage()
    {
        return round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
    }

    private function getCpuUsage()
    {
        // This would typically come from system monitoring
        return 'N/A'; // Placeholder
    }

    private function getDiskSpace()
    {
        try {
            $total = disk_total_space('/');
            $free = disk_free_space('/');
            $used = $total - $free;
            $percent = round(($used / $total) * 100, 1);
            return $percent . '%';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
} 