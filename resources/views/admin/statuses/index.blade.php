@extends('layouts.admin')

@section('title', 'Status Updates')
@section('page-title', 'Status Updates')

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
                       placeholder="Search statuses by user or content..." 
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
            <a href="{{ route('admin.statuses.export', request()->query()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-download-simple text-lg"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
        </div>
    </div>

    <!-- Filter Panel (Hidden by default) -->
    <div id="filter-panel" class="hidden bg-white border border-gray-100 rounded-2xl p-5 mb-6 shadow-sm">
        <form id="filter-form" method="GET" action="{{ route('admin.statuses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Preserve search if typed in main bar -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Type</label>
                <select name="type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Types</option>
                    <option value="text" {{ request('type') === 'text' ? 'selected' : '' }}>Text</option>
                    <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Image</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Sort By</label>
                <select name="sort_by" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                    <option value="views_count" {{ request('sort_by') === 'views_count' ? 'selected' : '' }}>Views</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Posted After</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                    Apply Filters
                </button>
                <a href="{{ route('admin.statuses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statuses Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires At</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($statuses as $status)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($status->user->avatar_url)
                                    <img src="{{ $status->user->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 shadow-sm">
                                        <i class="ph ph-user text-sm"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ $status->user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($status->type === 'text')
                                <div class="text-sm text-gray-600 truncate max-w-[200px]" style="background-color: {{ $status->background_color }}; color: {{ $status->text_font }}; padding: 4px 8px; border-radius: 4px;">
                                    {{ $status->content }}
                                </div>
                            @elseif($status->media_url)
                                <a href="{{ $status->media_url }}" target="_blank" class="block w-12 h-12 rounded-lg overflow-hidden border border-gray-200 relative group/media">
                                    @if($status->type === 'video')
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover/media:bg-black/30 transition-colors">
                                            <i class="ph ph-play-circle text-white text-xl"></i>
                                        </div>
                                        <video src="{{ $status->media_url }}" class="w-full h-full object-cover"></video>
                                    @else
                                        <img src="{{ $status->media_url }}" alt="Status Media" class="w-full h-full object-cover">
                                    @endif
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                @if($status->type === 'text')
                                    <i class="ph ph-text-t"></i>
                                @elseif($status->type === 'image')
                                    <i class="ph ph-image"></i>
                                @elseif($status->type === 'video')
                                    <i class="ph ph-video-camera"></i>
                                @endif
                                {{ ucfirst($status->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-sm text-gray-600">
                                <i class="ph ph-eye"></i>
                                {{ number_format($status->views_count) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $status->expires_at->format('M j, g:i A') }}</div>
                            <div class="text-xs text-gray-400">{{ $status->expires_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($status->expires_at->isPast())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                    <i class="ph ph-clock"></i>
                                    Expired
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                                    <i class="ph ph-check-circle"></i>
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 transition-opacity">
                                <a href="{{ route('admin.statuses.show', $status) }}" 
                                   class="p-2 text-gray-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                   title="View Details">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.statuses.destroy', $status) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this status? This action cannot be undone.')">
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
                                    <i class="ph ph-circle-dashed text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Statuses Found</h3>
                                <p class="text-sm text-gray-500 mb-4">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($statuses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $statuses->links() }}
        </div>
        @endif
    </div>
@endsection


