@extends('layouts.admin')

@section('title', 'Message Management')
@section('page-title', 'Message Management')

@section('toolbar-buttons')
    <a href="{{ route('admin.messages.create') }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-plus mr-2"></i> Create Message
    </a>
    <a href="{{ route('admin.messages.export', request()->query()) }}"
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
            <h3 class="text-lg font-semibold text-gray-900">Filter & Search Messages</h3>
        </div>

        <form method="GET" action="{{ route('admin.messages.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-whatsapp-text mb-2">Search</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search messages..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
            </div>

            <div>
                <label for="message_type" class="block text-sm font-medium text-whatsapp-text mb-2">Message Type</label>
                <select id="message_type"
                        name="message_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Types</option>
                    <option value="text" {{ request('message_type') === 'text' ? 'selected' : '' }}>Text</option>
                    <option value="image" {{ request('message_type') === 'image' ? 'selected' : '' }}>Image</option>
                    <option value="video" {{ request('message_type') === 'video' ? 'selected' : '' }}>Video</option>
                    <option value="audio" {{ request('message_type') === 'audio' ? 'selected' : '' }}>Audio</option>
                    <option value="document" {{ request('message_type') === 'document' ? 'selected' : '' }}>Document</option>
                    <option value="location" {{ request('message_type') === 'location' ? 'selected' : '' }}>Location</option>
                </select>
            </div>

            <div>
                <label for="chat_id" class="block text-sm font-medium text-whatsapp-text mb-2">Chat</label>
                <select id="chat_id"
                        name="chat_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Chats</option>
                    @foreach($chats as $chat)
                        <option value="{{ $chat->id }}" {{ request('chat_id') == $chat->id ? 'selected' : '' }}>
                            {{ $chat->name ?: ($chat->type === 'private' ? 'Private Chat' : 'Unnamed Group') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-whatsapp-text mb-2">Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Status</option>
                    <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>Sending</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
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
                <label for="show_deleted" class="block text-sm font-medium text-whatsapp-text mb-2">Show Deleted</label>
                <select id="show_deleted"
                        name="show_deleted"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="0" {{ request('show_deleted') === '0' ? 'selected' : '' }}>Active Only</option>
                    <option value="1" {{ request('show_deleted') === '1' ? 'selected' : '' }}>Deleted Only</option>
                </select>
            </div>

            <div>
                <label for="sort_by" class="block text-sm font-medium text-whatsapp-text mb-2">Sort By</label>
                <select id="sort_by"
                        name="sort_by"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                    <option value="sender_id" {{ request('sort_by') === 'sender_id' ? 'selected' : '' }}>Sender</option>
                    <option value="message_type" {{ request('sort_by') === 'message_type' ? 'selected' : '' }}>Type</option>
                    <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
                <a href="{{ route('admin.messages.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-envelope text-whatsapp-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Messages Directory</h3>
                        <p class="text-sm text-whatsapp-text">Total: {{ number_format($messages->total()) }} messages</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-whatsapp-text">
                        Showing {{ $messages->firstItem() }}-{{ $messages->lastItem() }} of {{ $messages->total() }}
                    </div>
                    <span class="bg-whatsapp-light text-whatsapp-primary px-3 py-1 rounded-full text-sm font-medium">
                        {{ $messages->total() }} total
                    </span>
                </div>
            </div>
        </div>

        @if($messages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-whatsapp-light">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Message</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Sender</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Chat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Sent At</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($messages as $message)
                        <tr class="hover:bg-gray-50 transition-colors {{ $message->is_deleted ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-whatsapp-light rounded-full flex items-center justify-center">
                                        @switch($message->message_type)
                                            @case('text')
                                                <i class="fas fa-comment text-whatsapp-primary"></i>
                                                @break
                                            @case('image')
                                                <i class="fas fa-image text-green-600"></i>
                                                @break
                                            @case('video')
                                                <i class="fas fa-video text-blue-600"></i>
                                                @break
                                            @case('audio')
                                                <i class="fas fa-microphone text-yellow-600"></i>
                                                @break
                                            @case('document')
                                                <i class="fas fa-file text-gray-600"></i>
                                                @break
                                            @case('location')
                                                <i class="fas fa-map-marker-alt text-red-600"></i>
                                                @break
                                            @default
                                                <i class="fas fa-comment text-gray-400"></i>
                                        @endswitch
                                    </div>
                                    <div>
                                        @if($message->message_type === 'text')
                                            <div class="font-medium text-gray-900">{{ Str::limit($message->content, 50) }}</div>
                                        @else
                                            <div class="font-medium text-gray-900">{{ ucfirst($message->message_type) }} Message</div>
                                            @if($message->file_name)
                                                <div class="text-sm text-whatsapp-text">{{ $message->file_name }}</div>
                                            @endif
                                        @endif
                                        @if($message->is_deleted)
                                            <div class="text-sm text-red-600 flex items-center">
                                                <i class="fas fa-trash mr-1"></i> Deleted
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $message->message_type === 'text' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas fa-{{ $message->message_type === 'text' ? 'comment' : ($message->message_type === 'image' ? 'image' : ($message->message_type === 'video' ? 'video' : ($message->message_type === 'audio' ? 'microphone' : ($message->message_type === 'document' ? 'file' : 'map-marker-alt')))) }} mr-1"></i>
                                    {{ ucfirst($message->message_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if($message->sender->avatar_url)
                                        <img src="{{ $message->sender->avatar_url }}"
                                             alt="Avatar"
                                             class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center">
                                            <span class="text-whatsapp-primary font-semibold">{{ strtoupper(substr($message->sender->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $message->sender->name }}</div>
                                        <div class="text-sm text-whatsapp-text">{{ $message->sender->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($message->chat)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $message->chat->type === 'group' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        <i class="fas fa-{{ $message->chat->type === 'group' ? 'users' : 'user' }} mr-1"></i>
                                        {{ $message->chat->name ?: ($message->chat->type === 'private' ? 'Private' : 'Group') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Deleted Chat
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @switch($message->status)
                                    @case('sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-paper-plane mr-1"></i>
                                            Sent
                                        </span>
                                        @break
                                    @case('delivered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Delivered
                                        </span>
                                        @break
                                    @case('read')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-eye mr-1"></i>
                                            Read
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ ucfirst($message->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $message->created_at->format('M j, Y') }}</div>
                                <div class="text-whatsapp-text">{{ $message->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.messages.show', $message) }}"
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($message->message_type === 'text' && !$message->is_deleted)
                                        <a href="{{ route('admin.messages.edit', $message) }}"
                                           class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 p-2 rounded-lg transition-colors"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if(!$message->is_deleted)
                                        <form method="POST"
                                              action="{{ route('admin.messages.destroy', $message) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this message?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-lg transition-colors"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.messages.restore', $message->id) }}"
                                           class="bg-green-100 hover:bg-green-200 text-green-600 p-2 rounded-lg transition-colors"
                                           title="Restore"
                                           onclick="return confirm('Are you sure you want to restore this message?')">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                        </table>
                    </div>
                @else
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="bg-whatsapp-light p-4 rounded-full mb-4">
                                <i class="fas fa-envelope text-whatsapp-primary text-4xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Messages Found</h3>
                            <p class="text-whatsapp-text mb-4">No messages match your current search criteria.</p>
                            <a href="{{ route('admin.messages.create') }}"
                               class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center font-medium">
                                <i class="fas fa-plus mr-2"></i> Send First Message
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
            </table>
        </div>

        @if($messages->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-whatsapp-text">
                    Showing <span class="font-medium">{{ $messages->firstItem() }}</span> to <span class="font-medium">{{ $messages->lastItem() }}</span> of <span class="font-medium">{{ $messages->total() }}</span> results
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    @if($messages->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </span>
                    @else
                        <a href="{{ $messages->previousPageUrl() }}"
                           class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-whatsapp-light hover:bg-whatsapp-primary hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @foreach($messages->getUrlRange(1, $messages->lastPage()) as $page => $url)
                        @if($page == $messages->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-whatsapp-primary rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-white hover:bg-whatsapp-light rounded-lg transition-colors border border-gray-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next Button -->
                    @if($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}"
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