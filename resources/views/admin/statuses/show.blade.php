@extends('layouts.admin')

@section('title', 'Status Details')
@section('page-title', 'Status Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                @if($status->user && $status->user->avatar_url)
                    <img src="{{ $status->user->avatar_url }}" alt="{{ $status->user->name }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <i class="ph ph-user text-green-700 text-2xl"></i>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Status by {{ $status->user ? $status->user->name : 'Unknown User' }}
                </h2>
                <p class="text-sm text-gray-600">
                    Posted {{ $status->created_at->format('M d, Y g:i A') }}
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $status->expires_at > now() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="ph {{ $status->expires_at > now() ? 'ph-clock' : 'ph-check-circle' }} mr-1 text-xs"></i>
                        {{ $status->expires_at > now() ? 'Active' : 'Expired' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($status->type) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex space-x-2">
            <form action="{{ route('admin.statuses.destroy', $status) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                        onclick="return confirm('Are you sure you want to delete this status? This action cannot be undone.')">
                    <i class="ph ph-trash mr-2"></i>Delete Status
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $stats['total_views'] }}</div>
            <div class="text-sm text-blue-800">Total Views</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $stats['unique_viewers'] }}</div>
            <div class="text-sm text-green-800">Unique Viewers</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">{{ $stats['views_today'] }}</div>
            <div class="text-sm text-purple-800">Views Today</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $stats['time_remaining'] }}</div>
            <div class="text-sm text-orange-800">Time Remaining</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Content -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Content</h3>
            <div class="bg-gray-50 rounded-lg p-6 flex items-center justify-center min-h-[300px]">
                @if($status->type === 'text')
                    <div class="text-center p-8 rounded-xl w-full max-w-md" 
                         style="background-color: {{ $status->background_color }}; color: {{ $status->text_font }}; font-family: {{ $status->font_family }}">
                        <p class="text-xl font-medium">{{ $status->content }}</p>
                    </div>
                @elseif($status->type === 'image')
                    <div class="relative w-full max-w-md">
                        <img src="{{ $status->media_url }}" alt="Status Image" class="w-full h-auto rounded-lg shadow-sm">
                        @if($status->caption)
                            <div class="mt-2 text-center text-gray-700">{{ $status->caption }}</div>
                        @endif
                    </div>
                @elseif($status->type === 'video')
                    <div class="relative w-full max-w-md">
                        <video src="{{ $status->media_url }}" controls class="w-full h-auto rounded-lg shadow-sm"></video>
                        @if($status->caption)
                            <div class="mt-2 text-center text-gray-700">{{ $status->caption }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Viewers -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Viewers</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                @if($status->views->count() > 0)
                    <div class="space-y-3">
                        @foreach($status->views->take(10) as $view)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @if($view->viewer && $view->viewer->avatar_url)
                                        <img src="{{ $view->viewer->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ substr($view->viewer ? $view->viewer->name : 'U', 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $view->viewer ? $view->viewer->name : 'Unknown User' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Viewed {{ $view->viewed_at ? $view->viewed_at->diffForHumans() : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No views yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.statuses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <i class="ph ph-arrow-left mr-2"></i>Back to Statuses
        </a>
    </div>
</div>
@endsection
