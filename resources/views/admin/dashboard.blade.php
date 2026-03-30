@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Users -->
        <div class="stat-card bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Users</p>
                    <p class="text-xl font-bold text-gray-900" data-count="{{ $stats['total_users'] }}">0</p>
                    <div class="flex items-center mt-1">
                        <span class="text-xs text-green-700 font-medium">+{{ $stats['active_users_today'] }} active today</span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ph-bold ph-users text-green-700"></i>
                </div>
            </div>
        </div>

        <!-- Total Messages -->
        <div class="stat-card bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Messages</p>
                    <p class="text-xl font-bold text-gray-900" data-count="{{ $stats['total_messages'] }}">0</p>
                    <div class="flex items-center mt-1">
                        <span class="text-xs text-green-700 font-medium">+{{ $stats['messages_today'] }} today</span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ph-bold ph-chat-circle-dots text-green-700"></i>
                </div>
            </div>
        </div>

        <!-- Total Chats -->
        <div class="stat-card bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Chats</p>
                    <p class="text-xl font-bold text-gray-900" data-count="{{ $stats['total_chats'] }}">0</p>
                    <div class="flex items-center mt-1">
                        <span class="text-xs text-gray-500 font-medium">{{ $stats['private_chats'] }} pvt, {{ $stats['group_chats'] }} grp</span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ph-bold ph-chats text-green-700"></i>
                </div>
            </div>
        </div>

        <!-- Total Calls -->
        <div class="stat-card bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Calls</p>
                    <p class="text-xl font-bold text-gray-900" data-count="{{ $stats['total_calls'] }}">0</p>
                    <div class="flex items-center mt-1">
                        <span class="text-xs text-green-700 font-medium">+{{ $stats['calls_today'] }} today</span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ph-bold ph-phone text-green-700"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- User Growth Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">User Growth</h3>
                    <p class="text-xs text-gray-500">New users over the last 30 days</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Recent Users List (Replacing Top Products) -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Recent Users</h3>
            <div class="space-y-3">
                @foreach($recent_users->take(5) as $user)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-lg" alt="{{ $user->name }}">
                        @else
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-700 font-bold text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @if($user->is_online)
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        @else
                            <div class="w-2 h-2 bg-gray-300 rounded-full mr-1"></div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('admin.users.index') }}" class="text-xs text-green-700 hover:text-green-800 font-medium">View All Users</a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Recent Messages -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Recent Messages</h3>
            <div class="space-y-2">
                @foreach($recent_messages->take(5) as $message)
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-center space-x-2">
                        <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center">
                            @if($message->type === 'text')
                                <i class="ph-bold ph-chat-circle-text text-green-700 text-xs"></i>
                            @elseif($message->type === 'image')
                                <i class="ph-bold ph-image text-green-700 text-xs"></i>
                            @else
                                <i class="ph-bold ph-file text-green-700 text-xs"></i>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-900">{{ $message->sender->name }}</p>
                            <p class="text-xs text-gray-500 line-clamp-1">{{ Str::limit($message->content, 40) }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans(null, true, true) }}</span>
                </div>
                @endforeach
            </div>
             <div class="mt-4 text-center">
                <a href="{{ route('admin.messages.index') }}" class="text-xs text-green-700 hover:text-green-800 font-medium">View All Messages</a>
            </div>
        </div>

        <!-- Message Activity Chart -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Message Activity</h3>
            <div class="h-48">
                <canvas id="messageActivityChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Counter animation
    function animateCounter(element, target) {
        const duration = 1500;
        const increment = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }

    // Initialize counters
    window.addEventListener("load", () => {
        document.querySelectorAll("[data-count]").forEach((el) => {
            const target = parseInt(el.getAttribute("data-count"));
            if (target > 0) {
                setTimeout(() => animateCounter(el, target), 300);
            }
        });
    });

    // Format date for display
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart');
    const userGrowthData = @json($user_growth);

    // Fill in missing dates for the last 30 days
    function fillMissingDates(data, days) {
        const result = [];
        const dataMap = new Map(data.map(item => [item.date, item.count]));
        
        for (let i = days - 1; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            const dateStr = date.toISOString().split('T')[0];
            result.push({
                date: dateStr,
                count: dataMap.get(dateStr) || 0
            });
        }
        return result;
    }

    const filledUserGrowth = fillMissingDates(userGrowthData, 30);

    if (userGrowthCtx) {
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: filledUserGrowth.map(item => formatDate(item.date)),
                datasets: [{
                    label: 'New Users',
                    data: filledUserGrowth.map(item => item.count),
                    borderColor: '#15803d',
                    backgroundColor: 'rgba(21, 128, 61, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#15803d',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
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
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#15803d',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 11 },
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 10 },
                            maxRotation: 45,
                            minRotation: 45,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });
    }

    // Message Activity Chart
    const messageActivityCtx = document.getElementById('messageActivityChart');
    const messageActivityData = @json($message_activity);

    const filledMessageActivity = fillMissingDates(messageActivityData, 7);

    if (messageActivityCtx) {
        new Chart(messageActivityCtx, {
            type: 'bar',
            data: {
                labels: filledMessageActivity.map(item => formatDate(item.date)),
                datasets: [{
                    label: 'Messages',
                    data: filledMessageActivity.map(item => item.count),
                    backgroundColor: '#15803d',
                    borderRadius: 6,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#15803d',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 11 },
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush

