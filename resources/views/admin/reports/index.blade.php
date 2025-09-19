@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics Dashboard')

@section('toolbar-buttons')
    <a href="{{ route('admin.reports.export', ['type' => 'users']) }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-users mr-2"></i> Users Report
    </a>
    <a href="{{ route('admin.reports.export', ['type' => 'messages']) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-envelope mr-2"></i> Messages Report
    </a>
    <a href="{{ route('admin.reports.export', ['type' => 'chats']) }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-comments mr-2"></i> Chats Report
    </a>
    <a href="{{ route('admin.reports.export', ['type' => 'calls']) }}"
       class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-phone mr-2"></i> Calls Report
    </a>
@endsection

@section('content')
<!-- Quick Stats Cards -->
<div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-whatsapp-light p-2 rounded-lg">
            <i class="fas fa-chart-bar text-whatsapp-primary"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Platform Statistics</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-whatsapp-primary text-white rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-1">{{ $stats['total_users'] }}</h3>
                    <p class="text-white/80 text-sm">Total Users</p>
                    <div class="text-white/60 text-xs flex items-center mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>{{ $stats['weekly_users'] }} this week
                    </div>
                </div>
                <div class="text-white/60">
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="bg-green-600 text-white rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-1">{{ App\Models\Message::count() }}</h3>
                    <p class="text-white/80 text-sm">Total Messages</p>
                    <div class="text-white/60 text-xs flex items-center mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>{{ App\Models\Message::whereDate('created_at', today())->count() }} today
                    </div>
                </div>
                <div class="text-white/60">
                    <i class="fas fa-envelope fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="bg-blue-600 text-white rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-1">{{ App\Models\Chat::count() }}</h3>
                    <p class="text-white/80 text-sm">Total Chats</p>
                    <div class="text-white/60 text-xs flex items-center mt-2">
                        <i class="fas fa-users mr-1"></i>{{ App\Models\Chat::where('type', 'group')->count() }} groups
                    </div>
                </div>
                <div class="text-white/60">
                    <i class="fas fa-comments fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="bg-orange-600 text-white rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-1">{{ App\Models\Call::count() }}</h3>
                    <p class="text-white/80 text-sm">Total Calls</p>
                    <div class="text-white/60 text-xs flex items-center mt-2">
                        <i class="fas fa-check mr-1"></i>{{ App\Models\Call::where('status', 'ended')->count() }} completed
                    </div>
                </div>
                <div class="text-white/60">
                    <i class="fas fa-phone fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Overview -->
<div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-whatsapp-light p-2 rounded-lg">
            <i class="fas fa-chart-line text-whatsapp-primary"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Activity Overview (Last 30 Days)</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-100 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ App\Models\Message::count() }}</div>
            <div class="text-sm text-blue-800">Total Messages</div>
            <div class="text-xs text-blue-600 mt-1">Last 30 days</div>
        </div>
        <div class="bg-green-100 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['weekly_users'] }}</div>
            <div class="text-sm text-green-800">New Users</div>
            <div class="text-xs text-green-600 mt-1">This week</div>
        </div>
        <div class="bg-orange-100 rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-orange-600 mb-2">{{ App\Models\Call::count() }}</div>
            <div class="text-sm text-orange-800">Total Calls</div>
            <div class="text-xs text-orange-600 mt-1">Last 30 days</div>
        </div>
    </div>

    <div class="text-center">
        <p class="text-whatsapp-text mb-4">For detailed charts and analytics, please use the individual report exports above.</p>
    </div>
</div>

<!-- Real-time Metrics & System Health -->
<div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-whatsapp-light p-2 rounded-lg">
            <i class="fas fa-tachometer-alt text-whatsapp-primary"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">System Overview</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Real-time Activity -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-4">Real-time Activity</h4>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-whatsapp-primary rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">{{ App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->count() }}</div>
                    <div class="text-sm text-whatsapp-text">Online Now</div>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-envelope text-white text-lg"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">{{ App\Models\Message::whereDate('created_at', today())->count() }}</div>
                    <div class="text-sm text-whatsapp-text">Today's Messages</div>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-circle text-white text-lg"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">{{ App\Models\Status::where('expires_at', '>', now())->count() }}</div>
                    <div class="text-sm text-whatsapp-text">Active Stories</div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-4">System Health</h4>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-600">Server Performance</span>
                        <span class="text-sm text-green-600 font-medium">Excellent</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 92%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-600">Database Response</span>
                        <span class="text-sm text-green-600 font-medium">Good</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 88%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-600">Storage Usage</span>
                        <span class="text-sm text-orange-600 font-medium">65%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" style="width: 65%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Types -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center">
        <div class="w-16 h-16 bg-whatsapp-primary rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-users text-white text-2xl"></i>
        </div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">User Analytics</h4>
        <p class="text-whatsapp-text mb-4">
            Comprehensive user growth, engagement metrics, registration trends, and user behavior analysis.
        </p>
        <button class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium">
            <i class="fas fa-arrow-right mr-2"></i> View User Reports
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center">
        <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chart-line text-white text-2xl"></i>
        </div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Activity Reports</h4>
        <p class="text-whatsapp-text mb-4">
            Message activity, chat engagement, call statistics, and overall platform usage patterns.
        </p>
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium">
            <i class="fas fa-arrow-right mr-2"></i> View Activity Reports
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center">
        <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-tachometer-alt text-white text-2xl"></i>
        </div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Performance Metrics</h4>
        <p class="text-whatsapp-text mb-4">
            System performance, server health, database metrics, and application monitoring insights.
        </p>
        <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium">
            <i class="fas fa-arrow-right mr-2"></i> View Performance
        </button>
    </div>
</div>
@endsection
