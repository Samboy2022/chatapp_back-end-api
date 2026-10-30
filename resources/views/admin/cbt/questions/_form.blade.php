{{-- Shared by create and edit. --}}
@csrf
@include('admin.cbt._flash')

@php
    $options = old('options', $question->options ?? []);
    $subjectName = old('subject', $question->subject?->name);
    $topicName = old('topic', $question->topic?->name);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Question</h3>
            <textarea name="question" rows="4" required
                      class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                      placeholder="e.g. The volume of a cube of side 3 cm is:">{{ old('question', $question->question) }}</textarea>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-1">Options</h3>
            <p class="text-xs text-gray-500 mb-4">Fill in at least A and B. Tick the correct one.</p>
            <div class="space-y-3">
                @foreach (['A', 'B', 'C', 'D', 'E'] as $letter)
                    <label class="flex items-center gap-3">
                        <input type="radio" name="correct_option" value="{{ $letter }}" class="w-4 h-4 text-green-600"
                               @checked(old('correct_option', $question->correct_option) === $letter)>
                        <span class="w-6 font-semibold text-gray-700">{{ $letter }}.</span>
                        <input type="text" name="options[{{ $letter }}]" value="{{ $options[$letter] ?? '' }}"
                               placeholder="{{ $letter === 'E' ? 'Optional' : '' }}"
                               class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-1">Explanation</h3>
            <p class="text-xs text-gray-500 mb-3">Shown under "Why this answer" in the result sheet, and straight away in Practice Mode.</p>
            <textarea name="explanation" rows="3"
                      class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('explanation', $question->explanation) }}</textarea>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h3 class="font-semibold text-gray-900">Classification</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <input type="text" name="subject" list="cbt-subjects" value="{{ $subjectName }}" required
                       placeholder="e.g. Mathematics"
                       class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                <datalist id="cbt-subjects">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->name }}">
                    @endforeach
                </datalist>
                <p class="text-xs text-gray-500 mt-1">Pick one, or type a new name to add the subject.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Topic</label>
                <input type="text" name="topic" list="cbt-topics" value="{{ $topicName }}" placeholder="e.g. Algebra"
                       class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                <datalist id="cbt-topics">
                    @foreach ($topicsBySubject->flatten()->unique()->sort() as $topic)
                        <option value="{{ $topic }}">
                    @endforeach
                </datalist>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam</label>
                    <select name="exam_type" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                        <option value="">Any exam</option>
                        @foreach ($examTypes as $exam)
                            <option value="{{ $exam }}" @selected(old('exam_type', $question->exam_type) === $exam)>{{ strtoupper($exam) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <input type="number" name="year" min="1970" max="2100" value="{{ old('year', $question->year) }}" placeholder="e.g. 2021"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                </div>
            </div>
            <p class="text-xs text-gray-500">Set the year only for real past questions; it's what candidates filter by.</p>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', $question->is_active))>
                Show in the app
            </label>
        </div>

        <div class="flex flex-col gap-2">
            <button class="w-full px-4 py-2.5 bg-blue-800 text-white rounded-xl text-sm font-semibold hover:bg-blue-900">Save question</button>
            @unless ($question->exists)
                <button name="add_another" value="1" class="w-full px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50">Save and add another</button>
            @endunless
        </div>
    </div>
</div>
