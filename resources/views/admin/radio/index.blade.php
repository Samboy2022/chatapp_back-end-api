@extends('layouts.admin')

@section('title', 'Radio')
@section('page-title', 'Radio Management')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Currently on air -->
    <div class="mb-6 rounded-2xl p-5 bg-gradient-to-br from-green-700 to-green-900 text-white shadow-sm">
        <div class="flex items-center gap-4">
            @if ($live?->thumbnail_url)
                <img src="{{ $live->thumbnail_url }}" alt="" class="w-16 h-16 rounded-xl object-cover ring-2 ring-white/30">
            @else
                <div class="w-16 h-16 rounded-xl bg-white/15 flex items-center justify-center">
                    <i class="ph ph-radio text-3xl"></i>
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-white/20 text-[11px] font-bold tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> ON AIR
                    </span>
                </div>
                <p class="font-semibold truncate">{{ $live?->title ?? 'No station is currently live' }}</p>
                <p class="text-white/70 text-sm truncate">
                    {{ $live?->audio_url ?? 'Set a programme as Live to start broadcasting to the app.' }}
                </p>
            </div>

            <a href="{{ route('admin.radio.create') }}"
               class="shrink-0 flex items-center gap-2 px-4 py-2.5 bg-white text-green-800 rounded-xl text-sm font-semibold hover:bg-green-50 transition-colors">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">New programme</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total items', 'value' => $stats['total'], 'icon' => 'ph-stack'],
            ['label' => 'Programmes', 'value' => $stats['programs'], 'icon' => 'ph-microphone-stage'],
            ['label' => 'Archive', 'value' => $stats['archive'], 'icon' => 'ph-archive'],
            ['label' => 'Total plays', 'value' => $stats['plays'], 'icon' => 'ph-play-circle'],
        ] as $stat)
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-2 text-gray-500 text-xs font-medium mb-1">
                    <i class="ph {{ $stat['icon'] }} text-base"></i>
                    <span>{{ $stat['label'] }}</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.radio.index') }}"
          class="bg-white border border-gray-100 rounded-2xl p-4 mb-6 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by title, host or description"
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
        </div>

        <select name="type" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
            <option value="">All types</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <select name="status" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Visible</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-medium hover:bg-green-800 transition-colors">
                Filter
            </button>
        </div>
    </form>

    <!-- Programme list -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left font-semibold px-5 py-3">Programme</th>
                        <th class="text-left font-semibold px-5 py-3">Type</th>
                        <th class="text-left font-semibold px-5 py-3">Duration</th>
                        <th class="text-left font-semibold px-5 py-3">Plays</th>
                        <th class="text-left font-semibold px-5 py-3">Status</th>
                        <th class="text-right font-semibold px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($programs as $program)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($program->thumbnail_url)
                                        <img src="{{ $program->thumbnail_url }}" alt=""
                                             class="w-11 h-11 rounded-lg object-cover border border-gray-100">
                                    @else
                                        <div class="w-11 h-11 rounded-lg bg-green-50 text-green-700 flex items-center justify-center">
                                            <i class="ph ph-music-notes text-xl"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate max-w-xs">{{ $program->title }}</p>
                                        <p class="text-gray-500 text-xs truncate max-w-xs">
                                            {{ $program->host ? 'Hosted by ' . $program->host : ($program->description ?: '—') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3">
                                @php
                                    $badge = match ($program->type) {
                                        'live' => 'bg-red-50 text-red-700',
                                        'archive' => 'bg-amber-50 text-amber-700',
                                        default => 'bg-blue-50 text-blue-700',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ $types[$program->type] ?? $program->type }}
                                </span>
                            </td>

                            <td class="px-5 py-3 text-gray-600">
                                @if ($program->isLive())
                                    <span class="text-gray-400">Continuous</span>
                                @elseif ($program->duration_seconds)
                                    {{ gmdate($program->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $program->duration_seconds) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-5 py-3 text-gray-600">{{ number_format($program->play_count) }}</td>

                            <td class="px-5 py-3">
                                @if ($program->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Visible</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Hidden</span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @unless ($program->isLive())
                                        <form method="POST" action="{{ route('admin.radio.set-live', $program) }}">
                                            @csrf
                                            <button type="submit" title="Put on air"
                                                    class="p-2 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                <i class="ph ph-broadcast text-lg"></i>
                                            </button>
                                        </form>
                                    @endunless

                                    <form method="POST" action="{{ route('admin.radio.toggle-active', $program) }}">
                                        @csrf
                                        <button type="submit" title="{{ $program->is_active ? 'Hide from app' : 'Show in app' }}"
                                                class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                                            <i class="ph {{ $program->is_active ? 'ph-eye-slash' : 'ph-eye' }} text-lg"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.radio.edit', $program) }}" title="Edit"
                                       class="p-2 rounded-lg text-gray-500 hover:bg-green-50 hover:text-green-700 transition-colors">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.radio.destroy', $program) }}"
                                          onsubmit="return confirm('Delete “{{ $program->title }}”? The audio file and thumbnail will be removed too.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"
                                                class="p-2 rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <i class="ph ph-radio text-5xl text-gray-300"></i>
                                <p class="mt-3 text-gray-500">No radio programmes yet.</p>
                                <a href="{{ route('admin.radio.create') }}"
                                   class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-medium hover:bg-green-800 transition-colors">
                                    <i class="ph ph-plus"></i> Add the first one
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($programs->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
@endsection
