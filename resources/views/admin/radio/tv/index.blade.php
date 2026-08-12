@extends('layouts.admin')

@section('title', 'TV Channels')
@section('page-title', 'TV Channels')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.tv-channels.index') }}" class="relative flex-1 max-w-lg">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search channels"
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
        </form>

        <a href="{{ route('admin.tv-channels.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
            <i class="ph ph-plus text-lg"></i>
            <span>New channel</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($channels as $channel)
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <div class="relative aspect-video bg-gray-100">
                    @if ($channel->thumbnail_url)
                        <img src="{{ $channel->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="ph ph-television-simple text-4xl text-gray-300"></i>
                        </div>
                    @endif

                    @if ($channel->is_live)
                        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-600 text-white text-[11px] font-bold tracking-wide">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span> LIVE
                        </span>
                    @endif

                    @unless ($channel->is_active)
                        <span class="absolute top-3 right-3 px-2 py-1 rounded-full bg-gray-900/70 text-white text-[11px] font-medium">
                            Hidden
                        </span>
                    @endunless
                </div>

                <div class="p-4">
                    <p class="font-semibold text-gray-900 truncate">{{ $channel->title }}</p>
                    <p class="text-gray-500 text-xs truncate mt-0.5">{{ $channel->description ?: $channel->stream_url }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-400">{{ number_format($channel->view_count) }} views</span>

                        <div class="flex items-center gap-1">
                            <form method="POST" action="{{ route('admin.tv-channels.toggle-active', $channel) }}">
                                @csrf
                                <button type="submit" title="{{ $channel->is_active ? 'Hide from app' : 'Show in app' }}"
                                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                                    <i class="ph {{ $channel->is_active ? 'ph-eye-slash' : 'ph-eye' }} text-lg"></i>
                                </button>
                            </form>

                            <a href="{{ route('admin.tv-channels.edit', $channel) }}" title="Edit"
                               class="p-2 rounded-lg text-gray-500 hover:bg-green-50 hover:text-green-700 transition-colors">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </a>

                            <form method="POST" action="{{ route('admin.tv-channels.destroy', $channel) }}"
                                  onsubmit="return confirm('Delete “{{ $channel->title }}”?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete"
                                        class="p-2 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border border-gray-100 rounded-2xl py-16 text-center shadow-sm">
                <i class="ph ph-television-simple text-5xl text-gray-300"></i>
                <p class="mt-3 text-gray-500">No TV channels yet.</p>
                <a href="{{ route('admin.tv-channels.create') }}"
                   class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-medium hover:bg-green-800 transition-colors">
                    <i class="ph ph-plus"></i> Add the first one
                </a>
            </div>
        @endforelse
    </div>

    @if ($channels->hasPages())
        <div class="mt-6">{{ $channels->links() }}</div>
    @endif
@endsection
