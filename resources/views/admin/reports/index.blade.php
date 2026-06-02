@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900">Statistics & Analytics</h2>
            <p class="text-sm text-gray-500">View comprehensive analytics and system statistics</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.export', request()->query()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-download-simple text-lg"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Users Stats -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Users</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_users']) }}</h3>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="ph ph-users text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-green-600 font-medium">{{ $stats['active_users'] }} active</span>
                <span class="text-gray-400">•</span>
                <span class="text-gray-500">{{ $stats['weekly_users'] }} this week</span>
            </div>
        </div>

        <!-- Messages Stats -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Messages</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_messages']) }}</h3>
                </div>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i class="ph ph-chat-circle-text text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500">{{ number_format($stats['today_messages']) }} sent today</div>
        </div>

        <!-- Chats Stats -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Chats</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_chats']) }}</h3>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="ph ph-users-three text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500">{{ number_format($stats['group_chats']) }} group chats</div>
        </div>

        <!-- Calls Stats -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Calls</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_calls']) }}</h3>
                </div>
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <i class="ph ph-phone text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500">{{ number_format($stats['successful_calls']) }} successful</div>
        </div>
    </div>

    <!-- User Status Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">User Status Distribution</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Active Users</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($userStatusDistribution['active_users']) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['total_users'] > 0 ? ($userStatusDistribution['active_users'] / $stats['total_users'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Inactive Users</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($userStatusDistribution['inactive_users']) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gray-500 h-2 rounded-full" style="width: {{ $stats['total_users'] > 0 ? ($userStatusDistribution['inactive_users'] / $stats['total_users'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Status Updates</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="ph ph-circle-dashed text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Total Statuses</p>
                            <p class="text-xs text-gray-500">All time</p>
                        </div>
                    </div>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($stats['total_statuses']) }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                            <i class="ph ph-check-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Active Statuses</p>
                            <p class="text-xs text-gray-500">Not expired</p>
                        </div>
                    </div>
                    <span class="text-lg font-bold text-gray-900">{{ number_format($stats['active_statuses']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-semibold text-gray-900">Export Reports</h3>
            <p class="text-xs text-gray-500 mt-1">Download detailed reports in various formats</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex flex-col p-4 border border-gray-200 rounded-xl hover:border-green-600 hover:bg-green-50 transition-all group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-green-700 group-hover:text-white flex items-center justify-center transition-colors">
                            <i class="ph ph-users text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Users Report</h4>
                            <p class="text-xs text-gray-500">Export all users data</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('admin.reports.export', ['type' => 'users', 'format' => 'csv']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-csv"></i> CSV</a>
                        <a href="{{ route('admin.reports.export', ['type' => 'users', 'format' => 'pdf']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-pdf"></i> PDF</a>
                    </div>
                </div>

                <div class="flex flex-col p-4 border border-gray-200 rounded-xl hover:border-green-600 hover:bg-green-50 transition-all group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-green-50 text-green-600 group-hover:bg-green-700 group-hover:text-white flex items-center justify-center transition-colors">
                            <i class="ph ph-chat-circle-text text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Messages Report</h4>
                            <p class="text-xs text-gray-500">Export all messages data</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('admin.reports.export', ['type' => 'messages', 'format' => 'csv']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-csv"></i> CSV</a>
                        <a href="{{ route('admin.reports.export', ['type' => 'messages', 'format' => 'pdf']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-pdf"></i> PDF</a>
                    </div>
                </div>

                <div class="flex flex-col p-4 border border-gray-200 rounded-xl hover:border-green-600 hover:bg-green-50 transition-all group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-lg bg-orange-50 text-orange-600 group-hover:bg-green-700 group-hover:text-white flex items-center justify-center transition-colors">
                            <i class="ph ph-phone text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Calls Report</h4>
                            <p class="text-xs text-gray-500">Export all calls data</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('admin.reports.export', ['type' => 'calls', 'format' => 'csv']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-csv"></i> CSV</a>
                        <a href="{{ route('admin.reports.export', ['type' => 'calls', 'format' => 'pdf']) }}" class="flex-1 flex items-center justify-center gap-1 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-50"><i class="ph ph-file-pdf"></i> PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


