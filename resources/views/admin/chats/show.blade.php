@extends('layouts.admin')

@section('title', 'Chat Details')
@section('page-title', 'Chat Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                @if($chat->avatar_url)
                    <img src="{{ $chat->avatar_url }}" alt="{{ $chat->name }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <i class="ph {{ $chat->type === 'group' ? 'ph-users' : 'ph-user' }} text-blue-700 text-2xl"></i>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $chat->name ?: ($chat->type === 'private' ? 'Private Chat' : 'Unnamed Group') }}
                </h2>
                <p class="text-sm text-gray-600">
                    Created {{ $chat->created_at->format('M d, Y') }}
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ ($chat->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <i class="ph {{ ($chat->is_active ?? true) ? 'ph-check-circle' : 'ph-x-circle' }} mr-1 text-xs"></i>
                        {{ ($chat->is_active ?? true) ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ ucfirst($chat->type) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.chats.edit', $chat) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                <i class="ph ph-pencil-simple mr-2"></i>Edit
            </a>
            <form action="{{ route('admin.chats.toggleActive', $chat) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="{{ ($chat->is_active ?? true) ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="ph {{ ($chat->is_active ?? true) ? 'ph-pause' : 'ph-play' }} mr-2"></i>
                    {{ ($chat->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <form action="{{ route('admin.chats.destroy', $chat) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                        onclick="return confirm('Are you sure you want to delete this chat? This action cannot be undone.')">
                    <i class="ph ph-trash mr-2"></i>Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $stats['total_messages'] }}</div>
            <div class="text-sm text-blue-800">Total Messages</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $stats['messages_today'] }}</div>
            <div class="text-sm text-green-800">Messages Today</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">{{ $stats['active_participants'] }}</div>
            <div class="text-sm text-purple-800">Active Participants</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $stats['total_calls'] }}</div>
            <div class="text-sm text-orange-800">Total Calls</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Participants -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Participants</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                @if($chat->participants->count() > 0)
                    <div class="space-y-3">
                        @foreach($chat->participants as $participant)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @if($participant->user && $participant->user->avatar_url)
                                        <img src="{{ $participant->user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ substr($participant->user ? $participant->user->name : 'U', 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $participant->user ? $participant->user->name : 'Unknown User' }}
                                            @if($participant->role === 'admin')
                                                <span class="ml-2 text-xs text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded">Admin</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Joined {{ $participant->joined_at ? $participant->joined_at->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($participant->user && $participant->user->is_online)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Online
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No participants found</p>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            
            <!-- Recent Messages -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Messages</h4>
                @if($stats['recent_activity']->count() > 0)
                    <div class="space-y-3">
                        @foreach($stats['recent_activity'] as $message)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($message->sender && $message->sender->avatar_url)
                                        <img src="{{ $message->sender->avatar_url }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600">
                                            <i class="ph ph-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-900">{{ $message->sender ? $message->sender->name : 'Unknown' }}</span>
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600 mt-0.5">
                                        @if($message->type === 'text')
                                            {{ Str::limit($message->content, 50) }}
                                        @else
                                            <span class="italic text-gray-500">Sent a {{ $message->type }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent messages</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.chats.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <i class="ph ph-arrow-left mr-2"></i>Back to Chats
        </a>
    </div>
</div>
@endsection
