@extends('layouts.admin')

@section('title', 'Message Details')
@section('page-title', 'Message Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="ph ph-envelope-simple text-blue-700 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Message #{{ $message->id }}
                </h2>
                <p class="text-sm text-gray-600">
                    Sent {{ $message->created_at->format('M d, Y g:i A') }}
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Type: {{ ucfirst($message->type) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $message->read_at ? 'bg-blue-100 text-blue-800' : ($message->delivered_at ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ $message->read_at ? 'Read' : ($message->delivered_at ? 'Delivered' : 'Sent') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex space-x-2">
            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                        onclick="return confirm('Are you sure you want to delete this message? This action cannot be undone.')">
                    <i class="ph ph-trash mr-2"></i>Delete Message
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Message Content -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Content</h3>
            <div class="bg-gray-50 rounded-lg p-6">
                @if($message->type === 'text')
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $message->content }}</p>
                @elseif($message->type === 'image')
                    <img src="{{ $message->attachment_url }}" alt="Message Attachment" class="max-w-full rounded-lg shadow-sm">
                @elseif($message->type === 'video')
                    <video src="{{ $message->attachment_url }}" controls class="max-w-full rounded-lg shadow-sm"></video>
                @elseif($message->type === 'audio')
                    <audio src="{{ $message->attachment_url }}" controls class="w-full"></audio>
                @elseif($message->type === 'document')
                    <div class="flex items-center p-4 bg-white rounded-lg border border-gray-200">
                        <i class="ph ph-file-text text-2xl text-gray-400 mr-3"></i>
                        <div class="flex-1 truncate">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ basename($message->attachment_url) }}</p>
                        </div>
                        <a href="{{ $message->attachment_url }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Download
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sender & Chat Info -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Context</h3>
            
            <!-- Sender -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Sender</h4>
                <div class="flex items-center gap-3">
                    @if($message->sender && $message->sender->avatar_url)
                        <img src="{{ $message->sender->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
                            <i class="ph ph-user"></i>
                        </div>
                    @endif
                    <div>
                        <div class="font-medium text-gray-900">{{ $message->sender ? $message->sender->name : 'Unknown' }}</div>
                        <div class="text-xs text-gray-500">{{ $message->sender ? $message->sender->email : '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Chat -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Chat</h4>
                @if($message->chat)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="ph {{ $message->chat->type === 'group' ? 'ph-users' : 'ph-user' }}"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $message->chat->name ?: ($message->chat->type === 'private' ? 'Private Chat' : 'Group Chat') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $message->chat->participants->count() }} participants
                            </div>
                        </div>
                        <a href="{{ route('admin.chats.show', $message->chat) }}" class="ml-auto text-sm text-blue-600 hover:text-blue-700">
                            View Chat
                        </a>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Chat deleted</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.messages.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <i class="ph ph-arrow-left mr-2"></i>Back to Messages
        </a>
    </div>
</div>
@endsection
