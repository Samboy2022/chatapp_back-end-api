@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Profile Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-whatsapp-light rounded-full flex items-center justify-center">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <i class="fas fa-user text-whatsapp-primary text-2xl"></i>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-whatsapp-text">{{ $user->email }}</p>
                <p class="text-sm text-gray-600">{{ $user->phone_number }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $user->is_online ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas fa-circle mr-1 text-xs"></i>
                        {{ $user->is_online ? 'Online' : 'Offline' }}
                    </span>
                    <span class="text-sm text-gray-600">
                        Last seen: {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            @if($user->deleted_at)
                <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-unlock mr-2"></i>Unblock
                    </button>
                </form>
            @else
                <form action="{{ route('admin.users.toggle-block', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                            onclick="return confirm('Are you sure you want to block this user?')">
                        <i class="fas fa-ban mr-2"></i>Block
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $stats['total_chats'] }}</div>
            <div class="text-sm text-blue-800">Total Chats</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $stats['total_messages'] }}</div>
            <div class="text-sm text-green-800">Total Messages</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $stats['total_statuses'] }}</div>
            <div class="text-sm text-orange-800">Status Updates</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">{{ $stats['total_calls'] }}</div>
            <div class="text-sm text-purple-800">Total Calls</div>
        </div>
    </div>

    <!-- User Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">User ID</label>
                        <p class="text-sm text-gray-900">#{{ $user->id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Joined</label>
                        <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country Code</label>
                        <p class="text-sm text-gray-900">{{ $user->country_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">About</label>
                        <p class="text-sm text-gray-900">{{ $user->about ?? 'No about info' }}</p>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Privacy Settings</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-700">Last Seen Privacy:</span>
                        <span class="text-sm font-medium">{{ ucfirst($user->last_seen_privacy ?? 'everyone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-700">Profile Photo Privacy:</span>
                        <span class="text-sm font-medium">{{ ucfirst($user->profile_photo_privacy ?? 'everyone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-700">About Privacy:</span>
                        <span class="text-sm font-medium">{{ ucfirst($user->about_privacy ?? 'everyone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-700">Status Privacy:</span>
                        <span class="text-sm font-medium">{{ ucfirst($user->status_privacy ?? 'everyone') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-700">Read Receipts:</span>
                        <span class="text-sm font-medium">{{ ($user->read_receipts_enabled ?? true) ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>

            <!-- Recent Messages -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Messages</h4>
                @if($user->messages->count() > 0)
                    <div class="space-y-2">
                        @foreach($user->messages->take(3) as $message)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex-1">
                                    <span class="text-gray-700">{{ Str::limit($message->content ?? 'Media message', 30) }}</span>
                                    @if($message->chat)
                                        <span class="text-gray-500 ml-2">in {{ $message->chat->name ?? 'Private Chat' }}</span>
                                    @endif
                                </div>
                                <span class="text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent messages</p>
                @endif
            </div>

            <!-- Active Chats -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Active Chats</h4>
                @if($user->chats->count() > 0)
                    <div class="space-y-2">
                        @foreach($user->chats->take(3) as $chat)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700">{{ $chat->name ?? 'Private Chat' }}</span>
                                <span class="text-gray-500">{{ $chat->participants->count() }} members</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No active chats</p>
                @endif
            </div>

            <!-- Recent Status Updates -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Status Updates</h4>
                @if($user->statuses->count() > 0)
                    <div class="space-y-2">
                        @foreach($user->statuses->take(3) as $status)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700">{{ Str::limit($status->content ?? 'Status update', 25) }}</span>
                                <span class="text-gray-500">{{ $status->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent status updates</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
    </div>
</div>
@endsection