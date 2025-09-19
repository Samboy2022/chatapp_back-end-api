@extends('layouts.admin')

@section('title', 'Chat Management')
@section('page-title', 'Chat Management')

@section('toolbar-buttons')
    <a href="{{ route('admin.chats.create') }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-plus mr-2"></i> Create New Chat
    </a>
    <a href="{{ route('admin.chats.export', request()->query()) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
@endsection

@section('content')
    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-whatsapp-light p-2 rounded-lg">
                <i class="fas fa-filter text-whatsapp-primary"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Filter & Search Chats</h3>
        </div>

        <form id="filter-form" method="GET" action="{{ route('admin.chats.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-whatsapp-text mb-2">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search chats..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-whatsapp-text mb-2">Chat Type</label>
                    <select id="type"
                            name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                        <option value="">All Types</option>
                        <option value="private" {{ request('type') === 'private' ? 'selected' : '' }}>Private</option>
                        <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
                    </select>
                </div>

                <div>
                    <label for="min_participants" class="block text-sm font-medium text-whatsapp-text mb-2">Min Participants</label>
                    <input type="number"
                           id="min_participants"
                           name="min_participants"
                           value="{{ request('min_participants') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>

                <div>
                    <label for="max_participants" class="block text-sm font-medium text-whatsapp-text mb-2">Max Participants</label>
                    <input type="number"
                           id="max_participants"
                           name="max_participants"
                           value="{{ request('max_participants') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-whatsapp-text mb-2">From Date</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="{{ route('admin.chats.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-whatsapp-text mb-2">Sort By</label>
                        <select id="sort_by"
                                name="sort_by"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="participants_count" {{ request('sort_by') === 'participants_count' ? 'selected' : '' }}>Participants</option>
                            <option value="messages_count" {{ request('sort_by') === 'messages_count' ? 'selected' : '' }}>Messages</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-whatsapp-text mb-2">Order</label>
                        <select id="sort_order"
                                name="sort_order"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                            <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center font-medium">
                            <i class="fas fa-sort mr-2"></i>
                            Apply Filter & Sort
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Chats Table -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-comments text-whatsapp-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Chats Directory</h3>
                        <p class="text-sm text-whatsapp-text">Total: {{ number_format($chats->total()) }} chats</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-whatsapp-text">
                        Showing {{ $chats->firstItem() }}-{{ $chats->lastItem() }} of {{ $chats->total() }}
                    </div>
                    <span class="bg-whatsapp-light text-whatsapp-primary px-3 py-1 rounded-full text-sm font-medium">
                        {{ $chats->total() }} total
                    </span>
                </div>
            </div>
        </div>

        @if($chats->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-whatsapp-light">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Chat Info</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Participants</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Messages</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Max Limit</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Created</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($chats as $chat)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    @if($chat->avatar_url)
                                        <img src="{{ $chat->avatar_url }}"
                                             alt="Chat Avatar"
                                             class="w-12 h-12 rounded-full">
                                    @else
                                        <div class="w-12 h-12 bg-whatsapp-light rounded-full flex items-center justify-center">
                                            <i class="fas fa-{{ $chat->type === 'group' ? 'users' : 'user' }} text-whatsapp-primary"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $chat->name ?: ($chat->type === 'private' ? 'Private Chat' : 'Unnamed Group') }}
                                        </div>
                                        @if($chat->description)
                                            <div class="text-sm text-whatsapp-text">{{ Str::limit($chat->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $chat->type === 'group' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    <i class="fas fa-{{ $chat->type === 'group' ? 'users' : 'user' }} mr-1"></i>
                                    {{ ucfirst($chat->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $chat->participants_count }} participants
                                    </span>
                                    @if($chat->participants_count >= ($chat->max_participants * 0.8))
                                        <i class="fas fa-exclamation-triangle text-whatsapp-primary"
                                           title="Approaching participant limit"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-whatsapp-light text-whatsapp-primary">
                                    <i class="fas fa-envelope mr-1"></i>
                                    {{ number_format($chat->messages_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $chat->max_participants }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($chat->is_active ?? true)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle text-green-500 mr-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-circle text-red-500 mr-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $chat->created_at->format('M j, Y') }}</div>
                                <div class="text-whatsapp-text">{{ $chat->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.chats.show', $chat) }}"
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.chats.edit', $chat) }}"
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 p-2 rounded-lg transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.chats.toggleActive', $chat) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="p-2 rounded-lg transition-colors {{ $chat->is_active ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-600' : 'bg-green-100 hover:bg-green-200 text-green-600' }}"
                                                title="{{ $chat->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $chat->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('admin.chats.destroy', $chat) }}"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this chat? This action cannot be undone.')">
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
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="bg-whatsapp-light p-4 rounded-full mb-4">
                                <i class="fas fa-comments text-whatsapp-primary text-4xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Chats Found</h3>
                            <p class="text-whatsapp-text mb-4">No chats match your current search criteria.</p>
                            <a href="{{ route('admin.chats.create') }}"
                               class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center font-medium">
                                <i class="fas fa-plus mr-2"></i> Create First Chat
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
                </tbody>
            </table>
        </div>

        @if($chats->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-whatsapp-text">
                    Showing <span class="font-medium">{{ $chats->firstItem() }}</span> to <span class="font-medium">{{ $chats->lastItem() }}</span> of <span class="font-medium">{{ $chats->total() }}</span> results
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    @if($chats->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </span>
                    @else
                        <a href="{{ $chats->previousPageUrl() }}"
                           class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-whatsapp-light hover:bg-whatsapp-primary hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @foreach($chats->getUrlRange(1, $chats->lastPage()) as $page => $url)
                        @if($page == $chats->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-whatsapp-primary rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-white hover:bg-whatsapp-light rounded-lg transition-colors border border-gray-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next Button -->
                    @if($chats->hasMorePages())
                        <a href="{{ $chats->nextPageUrl() }}"
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
