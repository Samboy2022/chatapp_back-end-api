@extends('layouts.admin')

@section('title', 'Review Report')
@section('page-title', 'Review Report')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('admin.moderation.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 transition-colors">
            <i class="ph ph-arrow-left"></i> Back to moderation
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report detail -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Reason</p>
                        <p class="text-lg font-semibold text-gray-900 mt-0.5">{{ $report->reasonLabel() }}</p>
                    </div>
                    @php
                        $tone = match ($report->status) {
                            'pending' => 'bg-red-50 text-red-700',
                            'reviewing' => 'bg-blue-50 text-blue-700',
                            'resolved' => 'bg-green-50 text-green-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tone }}">
                        {{ $statuses[$report->status] ?? $report->status }}
                    </span>
                </div>

                @if ($report->details)
                    <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-700 leading-relaxed">
                        {{ $report->details }}
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">No extra detail was provided.</p>
                @endif

                @if ($report->message)
                    <div class="mt-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Reported message</p>
                        <div class="p-4 border border-amber-200 bg-amber-50/60 rounded-xl">
                            <p class="text-sm text-gray-800">{{ $report->message->content ?: '(media message)' }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $report->message->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endif

                <p class="text-xs text-gray-400 mt-5">
                    Filed {{ $report->created_at?->diffForHumans() }}
                    @if ($report->reviewer)
                        · last reviewed by {{ $report->reviewer->name }}
                        {{ $report->reviewed_at?->diffForHumans() }}
                    @endif
                </p>
            </div>

            <!-- Decision -->
            <form method="POST" action="{{ route('admin.moderation.status', $report) }}"
                  class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
                @csrf
                <h2 class="font-semibold text-gray-900">Decision</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($report->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Moderator notes</label>
                    <textarea name="moderator_notes" rows="3"
                              placeholder="What did you decide, and why?"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">{{ old('moderator_notes', $report->moderator_notes) }}</textarea>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
                    Save decision
                </button>
            </form>

            @if ($otherReports->isNotEmpty())
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900 mb-1">Other reports about this person</h2>
                    <p class="text-xs text-gray-500 mb-4">A pattern matters more than a single complaint.</p>
                    <div class="divide-y divide-gray-100">
                        @foreach ($otherReports as $other)
                            <a href="{{ route('admin.moderation.show', $other) }}"
                               class="flex items-center justify-between py-3 hover:bg-gray-50/70 transition-colors">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $other->reasonLabel() }}</p>
                                    <p class="text-xs text-gray-500">
                                        by {{ $other->reporter?->name ?? 'Unknown' }} · {{ $other->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <i class="ph ph-caret-right text-gray-400"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- People -->
        <div class="space-y-6">
            @foreach ([['Reported user', $report->reportedUser, true], ['Reported by', $report->reporter, false]] as [$label, $person, $actionable])
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3">{{ $label }}</p>

                    @if ($person)
                        <div class="flex items-center gap-3">
                            @if ($person->avatar_url)
                                <img src="{{ $person->avatar_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $person->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $person->email }}</p>
                            </div>
                        </div>

                        @if ($actionable)
                            @if ($person->is_blocked)
                                <p class="mt-3 text-xs font-medium text-red-600">
                                    <i class="ph ph-prohibit"></i> Account is suspended
                                </p>
                            @endif

                            <form method="POST" action="{{ route('admin.moderation.toggle-block', $person) }}" class="mt-4">
                                @csrf
                                <button type="submit"
                                        onsubmit="return confirm('Are you sure?')"
                                        class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors
                                               {{ $person->is_blocked
                                                  ? 'bg-white border border-green-200 text-green-700 hover:bg-green-50'
                                                  : 'bg-red-600 text-white hover:bg-red-700' }}">
                                    {{ $person->is_blocked ? 'Restore account' : 'Suspend account' }}
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="text-sm text-gray-400 italic">Account no longer exists.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
