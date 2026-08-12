@extends('layouts.admin')

@section('title', 'Blocks')
@section('page-title', 'Moderation')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('admin.moderation.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:border-green-600 hover:text-green-700 transition-colors">
            Reports
        </a>
        <a href="{{ route('admin.moderation.blocks') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-green-700 text-white">Blocks</a>
    </div>

    <form method="GET" action="{{ route('admin.moderation.blocks') }}" class="relative max-w-lg mb-6">
        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by either person"
               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 text-sm">
    </form>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left font-semibold px-5 py-3">Blocker</th>
                        <th class="text-left font-semibold px-5 py-3"></th>
                        <th class="text-left font-semibold px-5 py-3">Blocked</th>
                        <th class="text-left font-semibold px-5 py-3">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($blocks as $block)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($block->user?->avatar_url)
                                        <img src="{{ $block->user->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($block->user?->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate max-w-[160px]">{{ $block->user?->name ?? 'Deleted' }}</p>
                                        <p class="text-xs text-gray-500 truncate max-w-[160px]">{{ $block->user?->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3 text-gray-300">
                                <i class="ph ph-prohibit text-xl text-red-400"></i>
                            </td>

                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($block->contactUser?->avatar_url)
                                        <img src="{{ $block->contactUser->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($block->contactUser?->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate max-w-[160px]">{{ $block->contactUser?->name ?? 'Deleted' }}</p>
                                        <p class="text-xs text-gray-500 truncate max-w-[160px]">{{ $block->contactUser?->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $block->updated_at?->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <i class="ph ph-handshake text-5xl text-gray-300"></i>
                                <p class="mt-3 text-gray-500">Nobody has blocked anyone.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($blocks->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $blocks->links() }}</div>
        @endif
    </div>
@endsection
