@extends('layouts.admin')

@section('title', 'Status Management')
@section('page-title', 'Status Management')

@section('toolbar-buttons')
    <a href="{{ route('admin.statuses.create') }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-plus mr-2"></i> Create Status
    </a>
    <form action="{{ route('admin.statuses.cleanup-expired') }}" method="POST" class="inline ml-3">
        @csrf
        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium" onclick="return confirm('Are you sure you want to cleanup all expired statuses?')">
            <i class="fas fa-trash mr-2"></i> Cleanup Expired
        </button>
    </form>
    <a href="{{ route('admin.statuses.export', request()->query()) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-whatsapp-light p-2 rounded-lg">
                <i class="fas fa-filter text-whatsapp-primary"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Filter & Search Statuses</h3>
        </div>

        <form method="GET" action="{{ route('admin.statuses.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-whatsapp-text mb-2">Search</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search content, users..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
            </div>

            <div>
                <label for="content_type" class="block text-sm font-medium text-whatsapp-text mb-2">Content Type</label>
                <select id="content_type"
                        name="content_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Types</option>
                    <option value="text" {{ request('content_type') === 'text' ? 'selected' : '' }}>Text</option>
                    <option value="image" {{ request('content_type') === 'image' ? 'selected' : '' }}>Image</option>
                    <option value="video" {{ request('content_type') === 'video' ? 'selected' : '' }}>Video</option>
                </select>
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-whatsapp-text mb-2">User</label>
                <select id="user_id"
                        name="user_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-whatsapp-text mb-2">Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
                <a href="{{ route('admin.statuses.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Statuses Table -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-circle text-whatsapp-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Status Updates</h3>
                        <p class="text-sm text-whatsapp-text">Total: {{ number_format($statuses->total()) }} statuses</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-whatsapp-text">
                        Showing {{ $statuses->firstItem() }}-{{ $statuses->lastItem() }} of {{ $statuses->total() }}
                    </div>
                    <span class="bg-whatsapp-light text-whatsapp-primary px-3 py-1 rounded-full text-sm font-medium">
                        {{ $statuses->total() }} total
                    </span>
                </div>
            </div>
        </div>

        @if($statuses->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-whatsapp-light">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Content</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Views</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Expires At</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($statuses as $status)
                        <tr class="hover:bg-gray-50 transition-colors {{ $status->expires_at <= now() ? 'bg-orange-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-whatsapp-light rounded-full flex items-center justify-center">
                                        @switch($status->content_type)
                                            @case('text')
                                                <i class="fas fa-comment text-whatsapp-primary"></i>
                                                @break
                                            @case('image')
                                                <i class="fas fa-image text-green-600"></i>
                                                @break
                                            @case('video')
                                                <i class="fas fa-video text-blue-600"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-gray-400"></i>
                                        @endswitch
                                    </div>
                                    <div>
                                        @if($status->content_type === 'text')
                                            <div class="font-medium text-gray-900">{{ Str::limit($status->content, 40) }}</div>
                                        @else
                                            <div class="font-medium text-gray-900">{{ ucfirst($status->content_type) }} Status</div>
                                            @if($status->media_url)
                                                <div class="text-sm text-whatsapp-text">Has media</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status->content_type === 'text' ? 'bg-blue-100 text-blue-800' : ($status->content_type === 'image' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                    <i class="fas fa-{{ $status->content_type === 'text' ? 'comment' : ($status->content_type === 'image' ? 'image' : 'video') }} mr-1"></i>
                                    {{ ucfirst($status->content_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if($status->user->avatar_url)
                                        <img src="{{ $status->user->avatar_url }}"
                                             alt="Avatar"
                                             class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-whatsapp-light rounded-full flex items-center justify-center">
                                            <span class="text-whatsapp-primary font-semibold">{{ strtoupper(substr($status->user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $status->user->name }}</div>
                                        <div class="text-sm text-whatsapp-text">{{ $status->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-eye mr-1"></i>
                                    {{ $status->views_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($status->expires_at > now())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                    <div class="text-sm text-whatsapp-text mt-1">{{ $status->expires_at->diffForHumans() }}</div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Expired
                                    </span>
                                    <div class="text-sm text-whatsapp-text mt-1">{{ $status->expires_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $status->created_at->format('M j, Y') }}</div>
                                <div class="text-whatsapp-text">{{ $status->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $status->expires_at->format('M j, Y') }}</div>
                                <div class="text-whatsapp-text">{{ $status->expires_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.statuses.show', $status) }}"
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($status->content_type === 'text')
                                        <a href="{{ route('admin.statuses.edit', $status) }}"
                                           class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 p-2 rounded-lg transition-colors"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($status->expires_at <= now())
                                        <form method="POST"
                                              action="{{ route('admin.statuses.extend', $status) }}"
                                              class="inline"
                                              onsubmit="return confirm('Extend this status by 24 hours?')">
                                            @csrf
                                            <input type="hidden" name="hours" value="24">
                                            <button type="submit"
                                                    class="bg-orange-100 hover:bg-orange-200 text-orange-600 p-2 rounded-lg transition-colors"
                                                    title="Extend 24h">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST"
                                          action="{{ route('admin.statuses.destroy', $status) }}"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this status?')">
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
                    <div class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="bg-whatsapp-light p-4 rounded-full mb-4">
                                <i class="fas fa-circle text-whatsapp-primary text-4xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Status Updates Found</h3>
                            <p class="text-whatsapp-text mb-4">No status updates match your current search criteria.</p>
                            <a href="{{ route('admin.statuses.create') }}"
                               class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center font-medium">
                                <i class="fas fa-plus mr-2"></i> Create First Status
                            </a>
                        </div>
                    </div>
                @endif
            </table>
        </div>

        @if($statuses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-whatsapp-text">
                    Showing <span class="font-medium">{{ $statuses->firstItem() }}</span> to <span class="font-medium">{{ $statuses->lastItem() }}</span> of <span class="font-medium">{{ $statuses->total() }}</span> results
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    @if($statuses->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </span>
                    @else
                        <a href="{{ $statuses->previousPageUrl() }}"
                           class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-whatsapp-light hover:bg-whatsapp-primary hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @foreach($statuses->getUrlRange(1, $statuses->lastPage()) as $page => $url)
                        @if($page == $statuses->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-whatsapp-primary rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-2 text-sm font-medium text-whatsapp-primary bg-white hover:bg-whatsapp-light rounded-lg transition-colors border border-gray-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next Button -->
                    @if($statuses->hasMorePages())
                        <a href="{{ $statuses->nextPageUrl() }}"
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
