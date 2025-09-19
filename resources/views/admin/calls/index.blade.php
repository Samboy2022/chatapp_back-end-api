@extends('layouts.admin')

@section('title', 'Call Management')
@section('page-title', 'Call Management')

@section('toolbar-buttons')
    <a href="{{ route('admin.calls.create') }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-plus mr-2"></i> Create Call Record
    </a>
    <a href="{{ route('admin.calls.export', request()->query()) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
@endsection

@section('content')
    <!-- Real-time Statistics Cards -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="bg-whatsapp-light p-2 rounded-lg">
                    <i class="fas fa-chart-line text-whatsapp-primary"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Real-time Statistics</h3>
            </div>
            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium" onclick="refreshStats()">
                <i class="fas fa-sync-alt mr-2"></i> Refresh Stats
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="realtime-stats">
            <div class="bg-whatsapp-primary text-white rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-medium text-white/80">Active Calls</h6>
                        <h3 class="text-2xl font-bold mb-1" id="active-calls-count">-</h3>
                        <small class="text-white/70">Currently in progress</small>
                    </div>
                    <div class="text-white/80">
                        <i class="fas fa-phone fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="bg-green-600 text-white rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-medium text-white/80">Calls Today</h6>
                        <h3 class="text-2xl font-bold mb-1" id="calls-today-count">-</h3>
                        <small class="text-white/70">Total calls today</small>
                    </div>
                    <div class="text-white/80">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="bg-blue-600 text-white rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-medium text-white/80">Success Rate</h6>
                        <h3 class="text-2xl font-bold mb-1" id="success-rate">-</h3>
                        <small class="text-white/70">Today's success rate</small>
                    </div>
                    <div class="text-white/80">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="bg-orange-600 text-white rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-medium text-white/80">Broadcast Status</h6>
                        <h3 class="text-sm font-bold mb-1" id="broadcast-status">-</h3>
                        <small class="text-white/70" id="broadcast-driver">-</small>
                    </div>
                    <div class="text-white/80">
                        <i class="fas fa-broadcast-tower fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Calls Monitor -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="bg-whatsapp-light p-2 rounded-lg">
                    <i class="fas fa-play-circle text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Active Calls Monitor</h3>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium" id="active-calls-badge">0 Active</span>
                <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium" onclick="refreshActiveCalls()">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>
        <div id="active-calls-container">
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-phone-slash text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text">No active calls at the moment</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <!-- <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="bg-whatsapp-light p-2 rounded-lg">
                    <i class="fas fa-history text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Recent Call Activity</h3>
            </div>
            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium" onclick="refreshRecentActivity()">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
        <div id="recent-activity-container">
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-whatsapp-primary mx-auto mb-4"></div>
                <p class="text-whatsapp-text">Loading recent activity...</p>
            </div>
        </div>
    </div> -->

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="bg-whatsapp-light p-2 rounded-lg">
                    <i class="fas fa-filter text-whatsapp-primary"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Filter & Search Calls</h3>
            </div>
    
            <form method="GET" action="{{ route('admin.calls.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-whatsapp-text mb-2">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search callers, chats..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>
    
                <div>
                    <label for="call_type" class="block text-sm font-medium text-whatsapp-text mb-2">Call Type</label>
                    <select id="call_type"
                            name="call_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                        <option value="">All Types</option>
                        <option value="voice" {{ request('call_type') === 'voice' ? 'selected' : '' }}>Voice</option>
                        <option value="video" {{ request('call_type') === 'video' ? 'selected' : '' }}>Video</option>
                    </select>
                </div>
    
                <div>
                    <label for="status" class="block text-sm font-medium text-whatsapp-text mb-2">Status</label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                        <option value="">All Status</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Answered</option>
                        <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>Missed</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
                    </select>
                </div>
    
                <div>
                    <label for="caller_id" class="block text-sm font-medium text-whatsapp-text mb-2">Caller</label>
                    <select id="caller_id"
                            name="caller_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                        <option value="">All Callers</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('caller_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
    
                <div>
                    <label for="date_from" class="block text-sm font-medium text-whatsapp-text mb-2">From Date</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>
    
                <div>
                    <label for="date_to" class="block text-sm font-medium text-whatsapp-text mb-2">To Date</label>
                    <input type="date"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>
    
                <div>
                    <label for="min_duration" class="block text-sm font-medium text-whatsapp-text mb-2">Min Duration (seconds)</label>
                    <input type="number"
                           id="min_duration"
                           name="min_duration"
                           value="{{ request('min_duration') }}"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>
    
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="{{ route('admin.calls.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                </div>
            </form>
        </div>

    <!-- Calls Table -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-phone text-whatsapp-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Calls Directory</h3>
                        <p class="text-sm text-whatsapp-text">Total: {{ number_format($calls->total()) }} calls</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-whatsapp-text">
                        Showing {{ $calls->firstItem() }}-{{ $calls->lastItem() }} of {{ $calls->total() }}
                    </div>
                    <span class="bg-whatsapp-light text-whatsapp-primary px-3 py-1 rounded-full text-sm font-medium">
                        {{ $calls->total() }} total
                    </span>
                </div>
            </div>
        </div>

        @if($calls->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-whatsapp-light">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Call Info</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Caller</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Chat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Started At</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($calls as $call)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-whatsapp-light rounded-full flex items-center justify-center">
                                        @if($call->call_type === 'video')
                                            <i class="fas fa-video text-blue-600"></i>
                                        @else
                                            <i class="fas fa-phone text-whatsapp-primary"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Call #{{ $call->id }}</div>
                                        <div class="text-sm text-whatsapp-text">{{ ($call->participants ? $call->participants->count() : 0) + 1 }} participants</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $call->call_type === 'video' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    <i class="fas fa-{{ $call->call_type === 'video' ? 'video' : 'phone' }} mr-1"></i>
                                    {{ ucfirst($call->call_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if($call->caller)
                                        @if($call->caller->avatar_url)
                                            <img src="{{ $call->caller->avatar_url }}"
                                                 alt="Avatar"
                                                 class="w-10 h-10 rounded-full">
                                        @else
                                            <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center">
                                                <span class="text-whatsapp-primary font-semibold">{{ strtoupper(substr($call->caller->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $call->caller->name }}</div>
                                            <div class="text-sm text-whatsapp-text">{{ $call->caller->email }}</div>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 font-semibold">?</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">Unknown Caller</div>
                                            <div class="text-sm text-whatsapp-text">No caller data</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($call->chat)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $call->chat->type === 'group' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        <i class="fas fa-{{ $call->chat->type === 'group' ? 'users' : 'user' }} mr-1"></i>
                                        {{ $call->chat->name ?: ($call->chat->type === 'private' ? 'Private' : 'Group') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        No Chat
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @switch($call->status)
                                    @case('answered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Answered
                                        </span>
                                        @break
                                    @case('missed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-phone-slash mr-1"></i>
                                            Missed
                                        </span>
                                        @break
                                    @case('declined')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>
                                            Declined
                                        </span>
                                        @break
                                    @case('failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Failed
                                        </span>
                                        @break
                                    @case('ended')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-stop mr-1"></i>
                                            Ended
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ ucfirst($call->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4">
                                @if($call->duration)
                                    @php
                                        $hours = floor($call->duration / 3600);
                                        $minutes = floor(($call->duration % 3600) / 60);
                                        $seconds = $call->duration % 60;

                                        $formatted = '';
                                        if ($hours > 0) $formatted .= $hours . 'h ';
                                        if ($minutes > 0) $formatted .= $minutes . 'm ';
                                        $formatted .= $seconds . 's';
                                    @endphp
                                    <span class="text-green-600 font-medium">{{ trim($formatted) }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $call->started_at ? $call->started_at->format('M j, Y') : '-' }}</div>
                                <div class="text-whatsapp-text">{{ $call->started_at ? $call->started_at->format('g:i A') : '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.calls.show', $call) }}"
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.calls.edit', $call) }}"
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 p-2 rounded-lg transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(in_array($call->status, ['initiated', 'ringing', 'answered']))
                                        <form method="POST"
                                              action="{{ route('admin.calls.end', $call) }}"
                                              class="inline"
                                              onsubmit="return confirm('End this call?')">
                                            @csrf
                                            <button type="submit"
                                                    class="bg-orange-100 hover:bg-orange-200 text-orange-600 p-2 rounded-lg transition-colors"
                                                    title="End Call">
                                                <i class="fas fa-phone-slash"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST"
                                          action="{{ route('admin.calls.destroy', $call) }}"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this call record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-lg transition-colors"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="bg-whatsapp-light p-4 rounded-full mb-4">
                                <i class="fas fa-phone text-whatsapp-primary text-4xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Calls Found</h3>
                            <p class="text-whatsapp-text mb-4">No calls match your current search criteria.</p>
                            <a href="{{ route('admin.calls.create') }}"
                               class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center font-medium">
                                <i class="fas fa-plus mr-2"></i> Create First Call Record
                            </a>
                        </div>
                    </div>
                @endif
            </table>
        </div>

        @if($calls->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-whatsapp-text">
                    Showing <span class="font-medium">{{ $calls->firstItem() }}</span> to <span class="font-medium">{{ $calls->lastItem() }}</span> of <span class="font-medium">{{ $calls->total() }}</span> results
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    @if($calls->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </span>
                    @else
                        <a href="{{ $calls->previousPageUrl() }}"
                           class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-whatsapp-light hover:bg-whatsapp-primary hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @foreach($calls->getUrlRange(1, $calls->lastPage()) as $page => $url)
                        @if($page == $calls->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-whatsapp-primary rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-white hover:bg-whatsapp-light rounded-lg transition-colors border border-gray-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next Button -->
                    @if($calls->hasMorePages())
                        <a href="{{ $calls->nextPageUrl() }}"
                           class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-whatsapp-light hover:bg-whatsapp-primary hover:text-white rounded-lg transition-colors">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        border-top: none;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .badge {
        font-size: 0.75em;
    }

    /* Real-time monitoring styles */
    .active-call-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .active-call-item:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .call-duration {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: #28a745;
    }

    .call-status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .realtime-update {
        animation: pulse 1s ease-in-out;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    .connection-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    .connection-indicator.connected {
        background-color: #28a745;
        box-shadow: 0 0 5px #28a745;
    }

    .connection-indicator.disconnected {
        background-color: #dc3545;
    }

    /* Recent Activity Styles */
    .activity-item {
        padding: 12px;
        border-radius: 8px;
        transition: background-color 0.2s;
    }

    .activity-item:hover {
        background-color: #f8f9fa;
    }

    .activity-item:not(:last-child) {
        border-bottom: 1px solid #eee;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
    }

    /* Pulse animation for active calls badge */
    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('📞 Call Management page loaded');

    // Check if CSRF token is available
    if (!document.querySelector('meta[name="csrf-token"]')) {
        console.warn('CSRF token not found. Adding it dynamically.');
        const metaTag = document.createElement('meta');
        metaTag.name = 'csrf-token';
        metaTag.content = '{{ csrf_token() }}';
        document.head.appendChild(metaTag);
    }

    // Initialize real-time monitoring
    initializeRealtimeMonitoring();

    // Start periodic updates
    startPeriodicUpdates();

    console.log('✅ Real-time call monitoring initialized');
});

/**
 * Initialize real-time monitoring
 */
function initializeRealtimeMonitoring() {
    // Load initial data
    loadRealtimeStats();
    loadActiveCalls();
    loadRecentActivity();
}

/**
 * Start periodic updates
 */
function startPeriodicUpdates() {
    // Update stats every 10 seconds
    setInterval(loadRealtimeStats, 10000);

    // Update active calls every 5 seconds
    setInterval(loadActiveCalls, 5000);

    // Update recent activity every 30 seconds
    setInterval(loadRecentActivity, 30000);
}

/**
 * Load real-time statistics
 */
async function loadRealtimeStats() {
    try {
        // Try the public API endpoint first
        let response = await fetch('/admin-api/calls/realtime-stats', {
            headers: {
                'Accept': 'application/json'
            }
        });

        // If that fails, try the authenticated endpoint
        if (response.status !== 200) {
            console.log('Falling back to authenticated endpoint for stats');
            response = await fetch('/admin/calls/realtime-stats', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (response.status === 401) {
                console.warn('Authentication required for realtime stats');
                return;
            }
        }

        const data = await response.json();

        if (data.success) {
            updateStatsDisplay(data.data);
        } else {
            console.warn('Failed to load realtime stats:', data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to load realtime stats:', error);
    }
}

/**
 * Load active calls
 */
async function loadActiveCalls() {
    try {
        // Try the public API endpoint first
        let response = await fetch('/admin-api/calls/active', {
            headers: {
                'Accept': 'application/json'
            }
        });

        // If that fails, try the authenticated endpoint
        if (response.status !== 200) {
            console.log('Falling back to authenticated endpoint for active calls');
            response = await fetch('/admin/calls/active', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (response.status === 401) {
                console.warn('Authentication required for active calls');
                return;
            }
        }

        const data = await response.json();

        if (data.success) {
            updateActiveCallsDisplay(data.data);
            updateActiveBadge(data.count);
        } else {
            console.warn('Failed to load active calls:', data.message || 'Unknown error');

            // Show empty state if there's an error
            const container = document.getElementById('active-calls-container');
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-whatsapp-primary text-xl"></i>
                    </div>
                    <p class="text-whatsapp-text mb-4">Could not load active calls</p>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium" onclick="refreshActiveCalls()">
                        <i class="fas fa-sync-alt mr-2"></i> Try Again
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load active calls:', error);
    }
}

/**
 * Update statistics display
 */
function updateStatsDisplay(stats) {
    // Add animation class
    document.getElementById('realtime-stats').classList.add('realtime-update');

    // Update values
    document.getElementById('active-calls-count').textContent = stats.active_calls || 0;
    document.getElementById('calls-today-count').textContent = stats.calls_today || 0;
    document.getElementById('success-rate').textContent = (stats.success_rate_today || 0) + '%';

    // Update broadcast status
    const broadcastStatus = document.getElementById('broadcast-status');
    const broadcastDriver = document.getElementById('broadcast-driver');

    if (stats.broadcast_status && stats.broadcast_status.enabled && stats.broadcast_status.connected) {
        broadcastStatus.innerHTML = '<span class="connection-indicator connected"></span>Online';
        broadcastDriver.textContent = (stats.broadcast_status.driver || 'unknown').toUpperCase() + ' Connected';

        if (stats.broadcast_status.message) {
            broadcastDriver.title = stats.broadcast_status.message;
        }
    } else if (stats.broadcast_status && stats.broadcast_status.enabled) {
        broadcastStatus.innerHTML = '<span class="connection-indicator disconnected"></span>Offline';
        broadcastDriver.textContent = (stats.broadcast_status.driver || 'unknown').toUpperCase() + ' Disconnected';

        if (stats.broadcast_status.message) {
            broadcastDriver.title = stats.broadcast_status.message;
        }
    } else {
        broadcastStatus.innerHTML = '<span class="connection-indicator disconnected"></span>Disabled';
        broadcastDriver.textContent = 'Broadcasting Disabled';
    }

    // Remove animation class after animation completes
    setTimeout(() => {
        document.getElementById('realtime-stats').classList.remove('realtime-update');
    }, 1000);
}

/**
 * Update active calls display
 */
function updateActiveCallsDisplay(activeCalls) {
    const container = document.getElementById('active-calls-container');

    // Handle null or undefined data
    if (!activeCalls || !Array.isArray(activeCalls) || activeCalls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-phone-slash text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text">No active calls at the moment</p>
            </div>
        `;
        return;
    }

    try {
        const callsHtml = activeCalls.map(call => {
            // Ensure call and required properties exist
            if (!call || !call.caller || !call.receiver) {
                console.warn('Invalid call data:', call);
                return '';
            }

            return `
                <div class="bg-white rounded-lg shadow-sm p-4 mb-3 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                        <div class="flex items-center space-x-3">
                            <img src="${call.caller.avatar || '/default-avatar.png'}"
                                  class="w-10 h-10 rounded-full"
                                  alt="${call.caller.name || 'Unknown'}">
                            <div>
                                <div class="font-medium text-gray-900">${call.caller.name || 'Unknown'}</div>
                                <div class="text-sm text-whatsapp-text">Caller</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <img src="${call.receiver.avatar || '/default-avatar.png'}"
                                  class="w-10 h-10 rounded-full"
                                  alt="${call.receiver.name || 'Unknown'}">
                            <div>
                                <div class="font-medium text-gray-900">${call.receiver.name || 'Unknown'}</div>
                                <div class="text-sm text-whatsapp-text">Receiver</div>
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(call.status || 'unknown')}">${(call.status || 'unknown').toUpperCase()}</span>
                            <div class="text-sm text-whatsapp-text mt-1">${(call.call_type || 'unknown').toUpperCase()}</div>
                        </div>
                        <div>
                            <div class="font-medium text-green-600">${call.duration_formatted || '00:00'}</div>
                            <div class="text-sm text-whatsapp-text">Duration</div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors" onclick="viewCallDetails('${call.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-lg transition-colors" onclick="endCall('${call.id}')">
                                <i class="fas fa-phone-slash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = callsHtml || `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-phone-slash text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text">No valid active calls found</p>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering active calls:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text mb-2">Error displaying active calls</p>
                <small class="text-red-600">${error.message}</small>
            </div>
        `;
    }
}

/**
 * Update active calls badge
 */
function updateActiveBadge(count) {
    const badge = document.getElementById('active-calls-badge');
    badge.textContent = `${count} Active`;

    if (count > 0) {
        badge.className = 'badge bg-success me-2 pulse-animation';
    } else {
        badge.className = 'badge bg-secondary me-2';
    }
}

/**
 * Get status badge CSS class
 */
function getStatusBadgeClass(status) {
    switch (status.toLowerCase()) {
        case 'ringing':
        case 'initiated':
            return 'bg-yellow-100 text-yellow-800';
        case 'answered':
            return 'bg-green-100 text-green-800';
        case 'ended':
            return 'bg-gray-100 text-gray-800';
        case 'declined':
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

/**
 * Refresh active calls manually
 */
function refreshActiveCalls() {
    loadActiveCalls();
    loadRealtimeStats();
}

/**
 * Refresh recent activity manually
 */
function refreshRecentActivity() {
    loadRecentActivity();
    showNotification('info', 'Refreshing recent activity...');
}

/**
 * Load recent call activity
 */
async function loadRecentActivity() {
    try {
        // Try the public API endpoint first
        let response = await fetch('/admin-api/calls/recent-activity', {
            headers: {
                'Accept': 'application/json'
            }
        });

        // If that fails, try the authenticated endpoint
        if (response.status !== 200) {
            console.log('Falling back to authenticated endpoint for recent activity');
            response = await fetch('/admin/calls/recent-activity', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (response.status === 401) {
                console.warn('Authentication required for recent activity');
                return;
            }
        }

        const data = await response.json();

        if (data.success) {
            updateRecentActivityDisplay(data.data);
        } else {
            console.warn('Failed to load recent activity:', data.message || 'Unknown error');

            // Show empty state if there's an error
            const container = document.getElementById('recent-activity-container');
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-whatsapp-primary text-xl"></i>
                    </div>
                    <p class="text-whatsapp-text mb-4">Could not load recent activity</p>
                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium" onclick="refreshRecentActivity()">
                        <i class="fas fa-sync-alt mr-2"></i> Try Again
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load recent activity:', error);
    }
}

/**
 * Update recent activity display
 */
function updateRecentActivityDisplay(recentCalls) {
    const container = document.getElementById('recent-activity-container');

    // Handle null or undefined data
    if (!recentCalls || !Array.isArray(recentCalls) || recentCalls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-history text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text">No recent call activity</p>
            </div>
        `;
        return;
    }

    try {
        const activityHtml = recentCalls.map(call => {
            // Ensure call and required properties exist
            if (!call || !call.caller || !call.receiver) {
                console.warn('Invalid call data:', call);
                return '';
            }

            // Determine status badge class
            const statusClass = getStatusBadgeClass(call.status || 'unknown');

            // Determine call type icon
            const callTypeIcon = call.call_type === 'video' ? 'bi-camera-video' : 'bi-telephone';

            return `
                <div class="bg-white rounded-lg shadow-sm p-4 mb-3 border border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="bg-whatsapp-light p-3 rounded-full">
                            <i class="fas ${callTypeIcon} text-whatsapp-primary text-lg"></i>
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">${call.caller.name || 'Unknown'}</span>
                                    <i class="fas fa-arrow-right text-whatsapp-text"></i>
                                    <span class="font-medium text-gray-900">${call.receiver.name || 'Unknown'}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${(call.status || 'unknown').toUpperCase()}</span>
                                </div>
                                <span class="text-sm text-whatsapp-text">${call.time_ago || 'Unknown time'}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-sm text-whatsapp-text">
                                    ${call.call_type === 'video' ? 'Video call' : 'Voice call'}
                                    ${call.duration_formatted ? `• ${call.duration_formatted}` : ''}
                                </span>
                                <button class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors" onclick="viewCallDetails('${call.id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = activityHtml || `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-history text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text">No recent call activity found</p>
            </div>
        `;
    } catch (error) {
        console.error('Error rendering recent activity:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="bg-whatsapp-light p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-whatsapp-primary text-xl"></i>
                </div>
                <p class="text-whatsapp-text mb-2">Error displaying recent activity</p>
                <small class="text-red-600">${error.message}</small>
            </div>
        `;
    }
}

/**
 * Refresh statistics manually
 */
function refreshStats() {
    loadRealtimeStats();
    showNotification('info', 'Refreshing statistics...');
}

/**
 * Show notification to user
 */
function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

/**
 * View call details
 */
function viewCallDetails(callId) {
    window.location.href = `/admin/calls/${callId}`;
}

/**
 * End an active call (admin action)
 */
async function endCall(callId) {
    if (!confirm('Are you sure you want to end this call?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/calls/${callId}/end`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('success', 'Call ended successfully');
            loadActiveCalls();
            loadRealtimeStats();
        } else {
            showNotification('error', data.message || 'Failed to end call');
        }
    } catch (error) {
        console.error('Failed to end call:', error);
        showNotification('error', 'Failed to end call');
    }
}

/**
 * Show notification
 */
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="bi bi-${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endpush