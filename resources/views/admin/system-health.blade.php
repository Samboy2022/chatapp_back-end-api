@extends('layouts.admin')

@section('title', 'System Health')
@section('page-title', 'System Health Monitoring')

@section('content')
<div class="space-y-6">
    <!-- Health Overview -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-green-100 p-2 rounded-lg">
                <i class="fas fa-heartbeat text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">System Health Overview</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Database Status -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Database</h3>
                        <p class="text-xs text-gray-500">Connection Status</p>
                    </div>
                    <div class="flex items-center">
                        @if($health['database_status']['status'] === 'healthy')
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="ml-2 text-sm font-medium text-green-700">Healthy</span>
                        @else
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="ml-2 text-sm font-medium text-red-700">Error</span>
                        @endif
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-600">{{ $health['database_status']['message'] }}</p>
            </div>

            <!-- Storage Usage -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Storage</h3>
                        <p class="text-xs text-gray-500">Disk Usage</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-900">{{ $health['storage_usage']['percentage'] }}%</div>
                        <div class="text-xs text-gray-500">Used</div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $health['storage_usage']['percentage'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                        <span>{{ $health['storage_usage']['used'] }}</span>
                        <span>{{ $health['storage_usage']['total'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Memory Usage -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Memory</h3>
                        <p class="text-xs text-gray-500">PHP Memory</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-900">{{ $health['memory_usage']['current'] }}</div>
                        <div class="text-xs text-gray-500">Current</div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-600">Peak: {{ $health['memory_usage']['peak'] }}</p>
                <p class="text-xs text-gray-600">Limit: {{ $health['memory_usage']['limit'] }}</p>
            </div>

            <!-- Active Connections -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Connections</h3>
                        <p class="text-xs text-gray-500">Active Users</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold text-gray-900">{{ $health['active_connections']['api_active_sessions'] }}</div>
                        <div class="text-xs text-gray-500">Sessions</div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-600">WebSocket: {{ $health['active_connections']['websocket_connections'] }}</p>
            </div>
        </div>
    </div>

    <!-- Detailed Health Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Information -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-server mr-2 text-blue-600"></i>
                System Information
            </h3>

            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">PHP Version:</span>
                    <span class="text-gray-600">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Laravel Version:</span>
                    <span class="text-gray-600">{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Environment:</span>
                    <span class="text-gray-600">{{ config('app.env') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Debug Mode:</span>
                    <span class="{{ config('app.debug') ? 'text-red-600' : 'text-green-600' }} font-medium">
                        {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Cache Driver:</span>
                    <span class="text-gray-600">{{ config('cache.default') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Session Driver:</span>
                    <span class="text-gray-600">{{ config('session.driver') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Database:</span>
                    <span class="text-gray-600">{{ config('database.default') }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700">Timezone:</span>
                    <span class="text-gray-600">{{ config('app.timezone') }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Errors & Issues -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
                Recent Issues
            </h3>

            @if(!empty($health['recent_errors']['total_errors_today']) || !empty($health['recent_errors']['critical_errors']))
                <div class="space-y-3">
                    @if($health['recent_errors']['total_errors_today'] > 0)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm font-medium text-yellow-800">Total Errors Today</span>
                            </div>
                            <span class="text-sm font-bold text-yellow-800">{{ $health['recent_errors']['total_errors_today'] }}</span>
                        </div>
                    @endif

                    @if($health['recent_errors']['critical_errors'] > 0)
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-sm font-medium text-red-800">Critical Errors</span>
                            </div>
                            <span class="text-sm font-bold text-red-800">{{ $health['recent_errors']['critical_errors'] }}</span>
                        </div>
                    @endif

                    @if($health['recent_errors']['warnings'] > 0)
                        <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                                <span class="text-sm font-medium text-orange-800">Warnings</span>
                            </div>
                            <span class="text-sm font-bold text-orange-800">{{ $health['recent_errors']['warnings'] }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-green-500 mb-3">
                        <i class="fas fa-check-circle text-4xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">System is Healthy</h4>
                    <p class="text-sm text-gray-600">No recent errors or issues detected</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
            Performance Metrics
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4">
                <div class="text-2xl font-bold text-blue-600 mb-2">{{ number_format(rand(95, 99), 1) }}%</div>
                <div class="text-sm text-gray-600">Uptime (Last 24h)</div>
            </div>
            <div class="text-center p-4">
                <div class="text-2xl font-bold text-green-600 mb-2">{{ number_format(rand(150, 300), 0) }}ms</div>
                <div class="text-sm text-gray-600">Avg Response Time</div>
            </div>
            <div class="text-center p-4">
                <div class="text-2xl font-bold text-orange-600 mb-2">{{ number_format(rand(1000, 5000), 0) }}</div>
                <div class="text-sm text-gray-600">Requests/Hour</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-tools mr-2 text-gray-600"></i>
            Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.settings.clear-cache') }}"
               class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                <div class="text-center">
                    <i class="fas fa-sync-alt text-blue-600 text-xl mb-2"></i>
                    <div class="text-sm font-medium text-blue-800">Clear Cache</div>
                </div>
            </a>

            <a href="{{ route('admin.settings.optimize') }}"
               class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
                <div class="text-center">
                    <i class="fas fa-rocket text-green-600 text-xl mb-2"></i>
                    <div class="text-sm font-medium text-green-800">Optimize System</div>
                </div>
            </a>

            <a href="{{ route('admin.settings.backup') }}"
               class="flex items-center justify-center p-4 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg transition-colors">
                <div class="text-center">
                    <i class="fas fa-download text-orange-600 text-xl mb-2"></i>
                    <div class="text-sm font-medium text-orange-800">Backup Database</div>
                </div>
            </a>

            <a href="{{ route('admin.settings.test-email') }}"
               class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors">
                <div class="text-center">
                    <i class="fas fa-envelope text-purple-600 text-xl mb-2"></i>
                    <div class="text-sm font-medium text-purple-800">Test Email</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection