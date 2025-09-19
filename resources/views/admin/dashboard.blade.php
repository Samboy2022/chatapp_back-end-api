@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

<!-- @section('toolbar-buttons')
    <a href="{{ route('admin.system-health') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-heartbeat mr-2"></i> System Health
    </a>
@endsection -->

@section('content')


    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-whatsapp-text uppercase tracking-wide">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_users']) }}</p>
                    <div class="flex items-center text-whatsapp-primary text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>
                        {{ $stats['active_users_today'] }} active today
                    </div>
                </div>
                <div class="bg-whatsapp-light rounded-xl p-4">
                    <i class="fas fa-users text-whatsapp-primary text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Messages -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-whatsapp-text uppercase tracking-wide">Total Messages</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_messages']) }}</p>
                    <div class="flex items-center text-whatsapp-primary text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>
                        {{ $stats['messages_today'] }} sent today
                    </div>
                </div>
                <div class="bg-whatsapp-light rounded-xl p-4">
                    <i class="fas fa-envelope text-whatsapp-primary text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Chats -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-whatsapp-text uppercase tracking-wide">Total Chats</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_chats']) }}</p>
                    <div class="text-whatsapp-text text-sm mt-2">
                        {{ $stats['private_chats'] }} private, {{ $stats['group_chats'] }} groups
                    </div>
                </div>
                <div class="bg-whatsapp-light rounded-xl p-4">
                    <i class="fas fa-comments text-whatsapp-primary text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Calls -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-whatsapp-text uppercase tracking-wide">Total Calls</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_calls']) }}</p>
                    <div class="flex items-center text-whatsapp-primary text-sm mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>
                        {{ $stats['calls_today'] }} made today
                    </div>
                </div>
                <div class="bg-whatsapp-light rounded-xl p-4">
                    <i class="fas fa-phone text-whatsapp-primary text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Status Updates -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center hover:shadow-xl transition-all duration-300">
            <div class="bg-whatsapp-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-camera text-whatsapp-primary text-2xl"></i>
            </div>
            <p class="font-semibold text-gray-900 mb-1">Status Updates</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_status_updates']) }}</p>
            <p class="text-sm text-whatsapp-text">{{ $stats['active_statuses'] }} active</p>
        </div>

        <!-- Total Contacts -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center hover:shadow-xl transition-all duration-300">
            <div class="bg-whatsapp-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-address-book text-whatsapp-primary text-2xl"></i>
            </div>
            <p class="font-semibold text-gray-900 mb-1">Total Contacts</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_contacts']) }}</p>
            <p class="text-sm text-whatsapp-text">{{ $stats['blocked_contacts'] }} blocked</p>
        </div>

        <!-- Active Users -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center hover:shadow-xl transition-all duration-300">
            <div class="bg-whatsapp-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-circle text-whatsapp-primary text-2xl"></i>
            </div>
            <p class="font-semibold text-gray-900 mb-1">Active Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_users_today']) }}</p>
            <p class="text-sm text-whatsapp-text">Today</p>
        </div>

        <!-- Group Chats -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 text-center hover:shadow-xl transition-all duration-300">
            <div class="bg-whatsapp-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-whatsapp-primary text-2xl"></i>
            </div>
            <p class="font-semibold text-gray-900 mb-1">Group Chats</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['group_chats']) }}</p>
            <p class="text-sm text-whatsapp-text">Active groups</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow lg:col-span-2">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-chart-line text-whatsapp-primary"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">User Growth (Last 30 Days)</h3>
                </div>
            </div>
            <div class="p-6">
                <canvas id="userGrowthChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Message Activity Chart -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-chart-bar text-whatsapp-primary"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Message Activity (Last 7 Days)</h3>
                </div>
            </div>
            <div class="p-6">
                <canvas id="messageActivityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-whatsapp-light p-2 rounded-lg">
                            <i class="fas fa-user-plus text-whatsapp-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                    </div>
                    <span class="px-3 py-1 bg-whatsapp-light text-whatsapp-primary rounded-full text-sm font-medium">
                        {{ $recent_users->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($recent_users as $index => $user)
                    <div class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-whatsapp-primary text-white text-xs font-bold rounded-full">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="w-10 h-10 rounded-full" alt="{{ $user->name }}">
                        @else
                            <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center">
                                <span class="text-whatsapp-primary font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-sm text-whatsapp-text truncate">{{ $user->email }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="flex items-center space-x-2 mb-1">
                                @if($user->is_online)
                                    <div class="w-2 h-2 bg-whatsapp-primary rounded-full"></div>
                                    <span class="text-xs text-whatsapp-primary">Online</span>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <span class="text-xs text-gray-600">Offline</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.users.index') }}"
                       class="bg-whatsapp-primary text-white px-6 py-3 rounded-lg hover:bg-whatsapp-dark transition-colors inline-flex items-center font-medium">
                        <i class="fas fa-users mr-2"></i>View All Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-whatsapp-light p-2 rounded-lg">
                            <i class="fas fa-comments text-whatsapp-primary"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Messages</h3>
                    </div>
                    <span class="px-3 py-1 bg-whatsapp-light text-whatsapp-primary rounded-full text-sm font-medium">
                        {{ $recent_messages->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($recent_messages as $index => $message)
                    <div class="flex items-start space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-whatsapp-primary text-white text-xs font-bold rounded-full">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="w-8 h-8 bg-whatsapp-light rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            @if($message->type === 'text')
                                <i class="fas fa-comment text-whatsapp-primary text-sm"></i>
                            @elseif($message->type === 'image')
                                <i class="fas fa-image text-whatsapp-primary text-sm"></i>
                            @elseif($message->type === 'video')
                                <i class="fas fa-video text-whatsapp-primary text-sm"></i>
                            @else
                                <i class="fas fa-file text-whatsapp-primary text-sm"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-medium text-gray-900 truncate">{{ $message->sender->name }}</p>
                                <span class="text-xs text-whatsapp-text flex-shrink-0 ml-2">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($message->type === 'text')
                                    <p class="text-whatsapp-text text-sm line-clamp-1">{{ Str::limit($message->content, 60) }}</p>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-whatsapp-light text-whatsapp-primary font-medium">
                                        <i class="fas fa-{{ $message->type === 'image' ? 'image' : ($message->type === 'video' ? 'video' : 'file') }} mr-1"></i>
                                        {{ ucfirst($message->type) }}
                                    </span>
                                @endif
                            </div>
                            @if($message->chat)
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-{{ $message->chat->type === 'group' ? 'users' : 'user' }} mr-1"></i>
                                    {{ $message->chat->type === 'group' ? 'Group: ' . $message->chat->name : 'Private Chat' }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.messages.index') }}"
                       class="bg-whatsapp-primary text-white px-6 py-3 rounded-lg hover:bg-whatsapp-dark transition-colors inline-flex items-center font-medium">
                        <i class="fas fa-envelope mr-2"></i>View All Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
const userGrowthData = @json($user_growth);

new Chart(userGrowthCtx, {
    type: 'line',
    data: {
        labels: userGrowthData.map(item => item.date),
        datasets: [{
            label: 'New Users',
            data: userGrowthData.map(item => item.count),
            borderColor: '#25D366',
            backgroundColor: 'rgba(37, 211, 102, 0.1)',
            tension: 0.1,
            fill: true,
            pointBackgroundColor: '#25D366',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(37, 211, 102, 0.1)'
                },
                ticks: {
                    color: '#128C7E'
                }
            },
            x: {
                grid: {
                    color: 'rgba(37, 211, 102, 0.1)'
                },
                ticks: {
                    color: '#128C7E'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#25D366',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#128C7E',
                borderWidth: 1
            }
        }
    }
});

// Message Activity Chart
const messageActivityCtx = document.getElementById('messageActivityChart').getContext('2d');
const messageActivityData = @json($message_activity);

new Chart(messageActivityCtx, {
    type: 'bar',
    data: {
        labels: messageActivityData.map(item => item.date),
        datasets: [{
            label: 'Messages',
            data: messageActivityData.map(item => item.count),
            backgroundColor: '#25D366',
            borderColor: '#128C7E',
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
            hoverBackgroundColor: '#128C7E'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(37, 211, 102, 0.1)'
                },
                ticks: {
                    color: '#128C7E'
                }
            },
            x: {
                grid: {
                    color: 'rgba(37, 211, 102, 0.1)'
                },
                ticks: {
                    color: '#128C7E'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#25D366',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#128C7E',
                borderWidth: 1
            }
        }
    }
});
</script>
@endpush