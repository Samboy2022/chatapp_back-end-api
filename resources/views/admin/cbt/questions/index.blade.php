@extends('layouts.admin')

@section('title', 'CBT Questions')
@section('page-title', 'CBT Questions')

@section('content')
    @include('admin.cbt._flash')

    <!-- Header -->
    <div class="mb-6 rounded-2xl p-5 bg-gradient-to-br from-blue-800 to-slate-900 text-white shadow-sm">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                <i class="ph ph-exam text-3xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold">Question bank for the app's CBT Practice</p>
                <p class="text-white/70 text-sm">
                    Tag a question with an exam (JAMB, WAEC, NECO) and year to offer it as a past question;
                    leave both empty for a general practice question every exam can use.
                </p>
            </div>
            <a href="{{ route('admin.cbt.questions.import') }}"
               class="shrink-0 flex items-center gap-2 px-4 py-2.5 bg-white/15 text-white rounded-xl text-sm font-semibold hover:bg-white/25 transition-colors">
                <i class="ph ph-upload-simple text-lg"></i><span>Import CSV</span>
            </a>
            <a href="{{ route('admin.cbt.questions.create') }}"
               class="shrink-0 flex items-center gap-2 px-4 py-2.5 bg-white text-blue-900 rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
                <i class="ph ph-plus text-lg"></i><span>New question</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([['Questions', $stats['questions'], 'ph-question', 'blue'], ['Subjects', $stats['subjects'], 'ph-books', 'green'], ['Topics', $stats['topics'], 'ph-list-bullets', 'amber'], ['Exams taken', $stats['attempts'], 'ph-student', 'purple']] as [$label, $value, $icon, $color])
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $color }}-50 flex items-center justify-center">
                        <i class="ph {{ $icon }} text-{{ $color }}-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($value) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search question text"
               class="lg:col-span-2 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
        <select name="subject_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-xl text-sm">
            <option value="">All subjects</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(($filters['subject_id'] ?? '') == $subject->id)>{{ $subject->name }} ({{ $subject->questions_count }})</option>
            @endforeach
        </select>
        <select name="topic_id" class="px-3 py-2 border border-gray-200 rounded-xl text-sm">
            <option value="">All topics</option>
            @foreach ($topics as $topic)
                <option value="{{ $topic->id }}" @selected(($filters['topic_id'] ?? '') == $topic->id)>{{ $topic->name }}</option>
            @endforeach
        </select>
        <select name="exam_type" class="px-3 py-2 border border-gray-200 rounded-xl text-sm">
            <option value="">All exams</option>
            <option value="any" @selected(($filters['exam_type'] ?? '') === 'any')>Untagged (any exam)</option>
            @foreach (\App\Models\CbtQuestion::EXAM_TYPES as $exam)
                <option value="{{ $exam }}" @selected(($filters['exam_type'] ?? '') === $exam)>{{ strtoupper($exam) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="year" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm">
                <option value="">All years</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-blue-800 text-white rounded-xl text-sm font-semibold hover:bg-blue-900">Filter</button>
        </div>
    </form>

    @if ($questions->isEmpty())
        <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
            <i class="ph ph-exam text-5xl text-gray-300"></i>
            <p class="mt-4 font-semibold text-gray-900">No questions match</p>
            <p class="text-sm text-gray-500 mt-1">Add one, or import a CSV of past questions.</p>
        </div>
    @else
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="text-left px-4 py-3">Question</th>
                            <th class="text-left px-4 py-3">Subject / topic</th>
                            <th class="text-left px-4 py-3">Exam</th>
                            <th class="text-left px-4 py-3">Answer</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($questions as $question)
                            <tr class="{{ $question->is_active ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3 max-w-md">
                                    <p class="text-gray-900 line-clamp-2">{{ $question->question }}</p>
                                    @unless ($question->is_active)
                                        <span class="text-xs text-gray-500">Hidden from the app</span>
                                    @endunless
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $question->subject?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $question->topic?->name ?? 'No topic' }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $question->exam_type ? strtoupper($question->exam_type) : 'Any' }}
                                    @if ($question->year) <span class="text-gray-500">· {{ $question->year }}</span> @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-green-700">{{ $question->correct_option }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.cbt.questions.edit', $question) }}" class="inline-flex p-2 text-gray-500 hover:text-blue-700" title="Edit">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.cbt.questions.destroy', $question) }}" class="inline"
                                          onsubmit="return confirm('Delete this question? Past results keep their scores.')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-gray-500 hover:text-red-600" title="Delete"><i class="ph ph-trash text-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $questions->links() }}</div>
    @endif
@endsection
