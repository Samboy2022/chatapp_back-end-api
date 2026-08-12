@extends('layouts.admin')

@section('title', 'Moderation')
@section('page-title', 'Moderation')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Open reports', 'value' => $stats['open'], 'icon' => 'ph-warning-circle', 'tone' => 'text-red-600'],
            ['label' => 'Awaiting review', 'value' => $stats['pending'], 'icon' => 'ph-hourglass', 'tone' => 'text-amber-600'],
            ['label' => 'Resolved', 'value' => $stats['resolved'], 'icon' => 'ph-check-circle', 'tone' => 'text-green-600'],
            ['label' => 'Active blocks', 'value' => $stats['blocks'], 'icon' => 'ph-prohibit', 'tone' => 'text-gray-600'],
        ] as $stat)
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i class="ph {{ $stat['icon'] }} text-base {{ $stat['tone'] }}"></i>
                    <span>{{ $stat['label'] }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('admin.moderation.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-green-700 text-white">Reports</a>
        <a href="{{ route('admin.moderation.blocks') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:border-green-600 hover:text-green-700 transition-colors">
            Blocks
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.moderation.index') }}"
          class="bg-white border border-gray-100 rounded-2xl p-4 mb-6 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by person or details"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 text-sm">
        </div>

        <select name="status" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
            <option value="">Any status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <select name="reason" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                <option value="">Any reason</option>
                @foreach ($reasons as $value => $label)
                    <option value="{{ $value }}" @selected(request('reason') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-medium hover:bg-green-800 transition-colors">
                Filter
            </button>
        </div>
    </form>

    <!-- Report list -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left font-semibold px-5 py-3">Reported</th>
                        <th class="text-left font-semibold px-5 py-3">Reason</th>
                        <th class="text-left font-semibold px-5 py-3">Reporter</th>
                        <th class="text-left font-semibold px-5 py-3">Status</th>
                        <th class="text-left font-semibold px-5 py-3">When</th>
                        <th class="text-right font-semibold px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($report->reportedUser?->avatar_url)
                                        <img src="{{ $report->reportedUser->avatar_url }}" alt=""
                                             class="w-9 h-9 rounded-full object-cover">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($report->reportedUser?->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate max-w-[180px]">
                                            {{ $report->reportedUser?->name ?? 'Deleted user' }}
                                        </p>
                                        @if ($report->blocked_by_reporter)
                                            <span class="text-[11px] text-gray-500">
                                                <i class="ph ph-prohibit"></i> also blocked
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                    {{ $report->reasonLabel() }}
                                </span>
                            </td>

                            <td class="px-5 py-3 text-gray-600 truncate max-w-[160px]">
                                {{ $report->reporter?->name ?? '—' }}
                            </td>

                            <td class="px-5 py-3">
                                @php
                                    $tone = match ($report->status) {
                                        'pending' => 'bg-red-50 text-red-700',
                                        'reviewing' => 'bg-blue-50 text-blue-700',
                                        'resolved' => 'bg-green-50 text-green-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $tone }}">
                                    {{ $statuses[$report->status] ?? $report->status }}
                                </span>
                            </td>

                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $report->created_at?->diffForHumans() }}
                            </td>

                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.moderation.show', $report) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-green-700 hover:bg-green-50 transition-colors">
                                    Review <i class="ph ph-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <i class="ph ph-shield-check text-5xl text-gray-300"></i>
                                <p class="mt-3 text-gray-500">No reports. Everything is quiet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $reports->links() }}</div>
        @endif
    </div>
@endsection
