@extends('layouts.admin')

@section('title', 'Call Details')
@section('page-title', 'Call Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="ph {{ $call->call_type === 'video' ? 'ph-video-camera' : 'ph-phone' }} text-blue-700 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Call #{{ $call->id }}
                </h2>
                <p class="text-sm text-gray-600">
                    Started {{ $call->started_at ? $call->started_at->format('M d, Y g:i A') : 'N/A' }}
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ ucfirst($call->call_type) }} Call
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $call->status === 'ended' ? 'bg-blue-100 text-blue-800' : ($call->status === 'missed' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                        {{ ucfirst($call->status) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex space-x-2">
            @if(in_array($call->status, ['initiated', 'ringing', 'answered']))
                <form action="{{ route('admin.calls.end', $call) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="return confirm('Are you sure you want to end this call?')">
                        <i class="ph ph-phone-slash mr-2"></i>End Call
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.calls.destroy', $call) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                        onclick="return confirm('Are you sure you want to delete this call? This action cannot be undone.')">
                    <i class="ph ph-trash mr-2"></i>Delete Record
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $stats['duration_formatted'] }}</div>
            <div class="text-sm text-blue-800">Duration</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $stats['total_participants'] }}</div>
            <div class="text-sm text-green-800">Total Participants</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">{{ $stats['answered_participants'] }}</div>
            <div class="text-sm text-purple-800">Answered</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $stats['missed_participants'] }}</div>
            <div class="text-sm text-orange-800">Missed</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Caller Info -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Caller Information</h3>
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center gap-4">
                    @if($call->caller && $call->caller->avatar_url)
                        <img src="{{ $call->caller->avatar_url }}" alt="" class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-2xl font-medium text-gray-600">
                            {{ substr($call->caller ? $call->caller->name : 'U', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $call->caller ? $call->caller->name : 'Unknown Caller' }}</h4>
                        <p class="text-gray-600">{{ $call->caller ? $call->caller->email : '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Participants</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                @if($call->participants->count() > 0)
                    <div class="space-y-3">
                        @foreach($call->participants as $participant)
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
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Role: {{ ucfirst($participant->role ?? 'participant') }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $participant->status === 'answered' ? 'bg-green-100 text-green-800' : ($participant->status === 'missed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($participant->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No other participants</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.calls.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <i class="ph ph-arrow-left mr-2"></i>Back to Calls
        </a>
    </div>
</div>
@endsection
