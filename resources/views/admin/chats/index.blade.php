@extends('layouts.admin')

@section('title', 'Chat Management')
@section('page-title', 'Chat Management')

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
                       placeholder="Search chats by name..." 
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
            <a href="{{ route('admin.chats.export', request()->query()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-600 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-download-simple text-lg"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
            <a href="{{ route('admin.chats.create') }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-green-700/30">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">Create Chat</span>
            </a>
        </div>
    </div>

    <!-- Filter Panel (Hidden by default) -->
    <div id="filter-panel" class="hidden bg-white border border-gray-100 rounded-2xl p-5 mb-6 shadow-sm">
        <form id="filter-form" method="GET" action="{{ route('admin.chats.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search chats..."
                       class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Chat Type</label>
                <select name="type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Types</option>
                    <option value="private" {{ request('type') === 'private' ? 'selected' : '' }}>Private</option>
                    <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Sort By</label>
                <select name="sort_by" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                    <option value="participants_count" {{ request('sort_by') === 'participants_count' ? 'selected' : '' }}>Participants</option>
                    <option value="messages_count" {{ request('sort_by') === 'messages_count' ? 'selected' : '' }}>Messages</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Created After</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                    Apply
                </button>
                <a href="{{ route('admin.chats.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Chats Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chat Info</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($chats as $chat)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($chat->avatar_url)
                                    <img src="{{ $chat->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 shadow-sm">
                                        <i class="ph {{ $chat->type === 'group' ? 'ph-users' : 'ph-user' }} text-xl"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ $chat->name ?: ($chat->type === 'private' ? 'Private Chat' : 'Unnamed Group') }}
                                    </div>
                                    @if($chat->description)
                                        <div class="text-xs text-gray-500 max-w-[200px] truncate">{{ $chat->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $chat->type === 'group' ? 'bg-purple-50 text-purple-600 border border-purple-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }}">
                                <i class="ph {{ $chat->type === 'group' ? 'ph-users' : 'ph-user' }}"></i>
                                {{ ucfirst($chat->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ $chat->participants_count }}</span>
                                @if($chat->max_participants)
                                    <span class="text-xs text-gray-400">/ {{ $chat->max_participants }}</span>
                                    @if($chat->participants_count >= ($chat->max_participants * 0.8))
                                        <i class="ph ph-warning text-orange-500" title="Approaching limit"></i>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="ph ph-envelope-simple"></i>
                                {{ number_format($chat->messages_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($chat->is_active ?? true)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                    <i class="ph ph-check-circle"></i>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                                    <i class="ph ph-x-circle"></i>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $chat->created_at->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $chat->created_at->format('g:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 transition-opacity">
                                <a href="{{ route('admin.chats.show', $chat) }}" 
                                   class="p-2 text-gray-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                   title="View Details">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                                <a href="{{ route('admin.chats.edit', $chat) }}" 
                                   class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                   title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.chats.toggleActive', $chat) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="p-2 text-gray-500 {{ ($chat->is_active ?? true) ? 'hover:text-orange-600 hover:bg-orange-50' : 'hover:text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                            title="{{ ($chat->is_active ?? true) ? 'Deactivate' : 'Activate' }}">
                                        <i class="ph {{ ($chat->is_active ?? true) ? 'ph-pause' : 'ph-play' }} text-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.chats.destroy', $chat) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this chat? This action cannot be undone.')">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="ph ph-chat-circle text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Chats Found</h3>
                                <p class="text-sm text-gray-500 mb-4">Try adjusting your search or filters</p>
                                <a href="{{ route('admin.chats.create') }}" class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                                    Create New Chat
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($chats->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $chats->links() }}
        </div>
        @endif
    </div>
@endsection


