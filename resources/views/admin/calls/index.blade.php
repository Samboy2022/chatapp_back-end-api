@extends('layouts.admin')

@section('title', 'Call Management')
@section('page-title', 'Call Management')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <!-- Search & Filter Toggle -->
        <div class="flex items-center gap-2 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                <input type="text" 
                       form="filter-form"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search calls..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
            </div>
            <button type="button" 
                    onclick="document.getElementById('filter-panel').classList.toggle('hidden')"
                    class="p-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-colors text-gray-600">
                <i class="ph ph-funnel text-lg"></i>
            </button>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button onclick="refreshStats(); refreshActiveCalls();" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
            <a href="{{ route('admin.calls.export', request()->query()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-download-simple text-lg"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
            <a href="{{ route('admin.calls.create') }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-green-700/30">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">Create Call</span>
            </a>
        </div>
    </div>

    <!-- Filter Panel (Hidden by default) -->
    <div id="filter-panel" class="hidden bg-white border border-gray-100 rounded-2xl p-5 mb-6 shadow-sm">
        <form id="filter-form" method="GET" action="{{ route('admin.calls.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Preserve search if typed in main bar -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Type</label>
                <select name="call_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Types</option>
                    <option value="voice" {{ request('call_type') === 'voice' ? 'selected' : '' }}>Voice</option>
                    <option value="video" {{ request('call_type') === 'video' ? 'selected' : '' }}>Video</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Status</option>
                    <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                    <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>Missed</option>
                    <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                    Apply Filters
                </button>
                <a href="{{ route('admin.calls.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Real-time Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="realtime-stats">
        <!-- Active Calls -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Calls</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1" id="active-calls-count">-</h3>
                </div>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i class="ph ph-phone-call text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-1 text-xs text-green-600 font-medium">
                <span class="relative flex h-2 w-2 mr-1">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Live Monitoring
            </div>
        </div>

        <!-- Calls Today -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Calls Today</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1" id="calls-today-count">-</h3>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="ph ph-calendar-check text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500">Total calls initiated today</div>
        </div>

        <!-- Success Rate -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1" id="success-rate">-</h3>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="ph ph-chart-line-up text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500">Completion rate for today</div>
        </div>

        <!-- Broadcast Status -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">System Status</p>
                    <h3 class="text-lg font-bold text-gray-900 mt-1" id="broadcast-status">-</h3>
                </div>
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <i class="ph ph-broadcast text-xl"></i>
                </div>
            </div>
            <div class="text-xs text-gray-500 truncate" id="broadcast-driver">-</div>
        </div>
    </div>

    <!-- Active Calls Monitor -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-gray-900">Active Calls Monitor</h3>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100" id="active-calls-badge">
                0 Active
            </span>
        </div>
        <div id="active-calls-container" class="p-6">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="ph ph-phone-slash text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 text-sm">No active calls at the moment</p>
            </div>
        </div>
    </div>

    <!-- Calls History Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Call Info</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caller</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chat</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started At</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($calls as $call)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 shadow-sm">
                                    <i class="ph {{ $call->call_type === 'video' ? 'ph-video-camera' : 'ph-phone' }} text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">Call #{{ $call->id }}</div>
                                    <div class="text-xs text-gray-500">{{ ($call->participants ? $call->participants->count() : 0) + 1 }} participants</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $call->call_type === 'video' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-green-50 text-green-600 border border-green-100' }}">
                                <i class="ph {{ $call->call_type === 'video' ? 'ph-video-camera' : 'ph-phone' }}"></i>
                                {{ ucfirst($call->call_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($call->caller)
                                <div class="flex items-center gap-2">
                                    @if($call->caller->avatar_url)
                                        <img src="{{ $call->caller->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ substr($call->caller->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="text-sm text-gray-900">{{ $call->caller->name }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($call->chat)
                                <a href="{{ route('admin.chats.show', $call->chat) }}" class="text-sm text-gray-600 hover:text-green-700 transition-colors">
                                    {{ $call->chat->name ?: ($call->chat->type === 'private' ? 'Private Chat' : 'Group Chat') }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400">No Chat</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @switch($call->status)
                                @case('answered')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                        <i class="ph ph-check"></i> Answered
                                    </span>
                                    @break
                                @case('missed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-600 border border-orange-100">
                                        <i class="ph ph-phone-slash"></i> Missed
                                    </span>
                                    @break
                                @case('declined')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                                        <i class="ph ph-x"></i> Declined
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <i class="ph ph-warning"></i> Failed
                                    </span>
                                    @break
                                @case('ended')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">
                                        <i class="ph ph-stop"></i> Ended
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                                        {{ ucfirst($call->status) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4">
                            @if($call->duration)
                                <span class="text-sm font-mono text-gray-600">
                                    {{ gmdate("H:i:s", $call->duration) }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $call->started_at ? $call->started_at->format('M j, Y') : '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $call->started_at ? $call->started_at->format('g:i A') : '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 transition-opacity">
                                <a href="{{ route('admin.calls.show', $call) }}" 
                                   class="p-2 text-gray-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                   title="View Details">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                                @if(in_array($call->status, ['initiated', 'ringing', 'answered']))
                                    <form method="POST" action="{{ route('admin.calls.end', $call) }}" class="inline" onsubmit="return confirm('End this call?')">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                                title="End Call">
                                            <i class="ph ph-phone-slash text-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.calls.destroy', $call) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this call? This action cannot be undone.')">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="ph ph-phone text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Calls Found</h3>
                                <p class="text-sm text-gray-500 mb-4">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($calls->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $calls->links() }}
        </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .active-call-item {
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .active-call-item:hover {
        border-color: #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .connection-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .connection-indicator.connected {
        background-color: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }
    .connection-indicator.disconnected {
        background-color: #ef4444;
    }
    .realtime-update {
        animation: pulse-bg 0.5s ease-in-out;
    }
    @keyframes pulse-bg {
        0% { background-color: transparent; }
        50% { background-color: rgba(34, 197, 94, 0.05); }
        100% { background-color: transparent; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if CSRF token is available
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const metaTag = document.createElement('meta');
        metaTag.name = 'csrf-token';
        metaTag.content = '{{ csrf_token() }}';
        document.head.appendChild(metaTag);
    }

    // Initialize real-time monitoring
    initializeRealtimeMonitoring();
    startPeriodicUpdates();
});

function initializeRealtimeMonitoring() {
    loadRealtimeStats();
    loadActiveCalls();
}

function startPeriodicUpdates() {
    setInterval(loadRealtimeStats, 10000);
    setInterval(loadActiveCalls, 5000);
}

async function loadRealtimeStats() {
    try {
        let response = await fetch('/admin-api/calls/realtime-stats', {
            headers: { 'Accept': 'application/json' }
        });

        if (response.status !== 200) {
            response = await fetch('/admin/calls/realtime-stats', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        }

        const data = await response.json();
        if (data.success) {
            updateStatsDisplay(data.data);
        }
    } catch (error) {
        console.error('Failed to load realtime stats:', error);
    }
}

async function loadActiveCalls() {
    try {
        let response = await fetch('/admin-api/calls/active', {
            headers: { 'Accept': 'application/json' }
        });

        if (response.status !== 200) {
            response = await fetch('/admin/calls/active', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        }

        const data = await response.json();
        if (data.success) {
            updateActiveCallsDisplay(data.data);
            updateActiveBadge(data.count);
        }
    } catch (error) {
        console.error('Failed to load active calls:', error);
    }
}

function updateStatsDisplay(stats) {
    const container = document.getElementById('realtime-stats');
    if(container) {
        container.classList.remove('realtime-update');
        void container.offsetWidth; // trigger reflow
        container.classList.add('realtime-update');
    }

    document.getElementById('active-calls-count').textContent = stats.active_calls || 0;
    document.getElementById('calls-today-count').textContent = stats.calls_today || 0;
    document.getElementById('success-rate').textContent = (stats.success_rate_today || 0) + '%';

    const broadcastStatus = document.getElementById('broadcast-status');
    const broadcastDriver = document.getElementById('broadcast-driver');

    if (stats.broadcast_status && stats.broadcast_status.enabled) {
        const isConnected = stats.broadcast_status.connected;
        broadcastStatus.innerHTML = `<span class="connection-indicator ${isConnected ? 'connected' : 'disconnected'}"></span>${isConnected ? 'Online' : 'Offline'}`;
        broadcastDriver.textContent = (stats.broadcast_status.driver || 'unknown').toUpperCase();
    } else {
        broadcastStatus.innerHTML = '<span class="connection-indicator disconnected"></span>Disabled';
        broadcastDriver.textContent = 'System Disabled';
    }
}

function updateActiveCallsDisplay(calls) {
    const container = document.getElementById('active-calls-container');
    
    if (!calls || calls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="ph ph-phone-slash text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 text-sm">No active calls at the moment</p>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-3">';
    calls.forEach(call => {
        html += `
            <div class="active-call-item flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="ph ${call.type === 'video' ? 'ph-video-camera' : 'ph-phone'} text-xl"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Call #${call.id}</div>
                        <div class="text-xs text-gray-500">${call.participants_count} participants • ${call.duration_formatted}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        Active
                    </span>
                    <form method="POST" action="/admin/calls/${call.id}/end" class="inline" onsubmit="return confirm('End this call?')">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="End Call">
                            <i class="ph ph-phone-slash text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function updateActiveBadge(count) {
    const badge = document.getElementById('active-calls-badge');
    if (badge) {
        badge.textContent = `${count} Active`;
        if (count > 0) {
            badge.classList.remove('bg-gray-100', 'text-gray-600');
            badge.classList.add('bg-green-100', 'text-green-700');
        } else {
            badge.classList.remove('bg-green-100', 'text-green-700');
            badge.classList.add('bg-gray-100', 'text-gray-600');
        }
    }
}

function refreshStats() {
    loadRealtimeStats();
}

function refreshActiveCalls() {
    loadActiveCalls();
}
</script>
@endpush

