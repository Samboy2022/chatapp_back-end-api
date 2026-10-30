@csrf
@include('admin.cbt._flash')

@php
    $target = old('cta_target', $slide->cta_target);
    $isExam = $target && array_key_exists($target, $targets);
    $kind = old('cta_kind', $target ? ($isExam ? 'exam' : 'url') : 'none');
@endphp

<div class="max-w-2xl bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Badge</label>
            <input type="text" name="badge" maxlength="30" value="{{ old('badge', $slide->badge) }}" placeholder="RECOMMENDED"
                   class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
            <input type="text" name="title" maxlength="80" required value="{{ old('title', $slide->title) }}" placeholder="Start Your Practice"
                   class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
        <input type="text" name="subtitle" maxlength="200" value="{{ old('subtitle', $slide->subtitle) }}"
               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Button</label>
            <select name="cta_kind" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                <option value="none" @selected($kind === 'none')>No button</option>
                <option value="exam" @selected($kind === 'exam')>Open an exam</option>
                <option value="url" @selected($kind === 'url')>Open a web page</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Exam</label>
            <select name="cta_exam" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                @foreach ($targets as $slug => $label)
                    <option value="{{ $slug }}" @selected($isExam && $target === $slug)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Button label</label>
            <input type="text" name="cta_label" maxlength="30" value="{{ old('cta_label', $slide->cta_label) }}" placeholder="Begin Now"
                   class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Web page (when the button opens a web page)</label>
        <input type="url" name="cta_url" value="{{ old('cta_url', $isExam ? '' : $target) }}" placeholder="https://"
               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
    </div>

    <p class="text-xs text-gray-500">The app draws slides in its own brand colour.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $slide->sort_order) }}"
                   class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', $slide->is_active))>
        Show in the app
    </label>

    <button class="px-4 py-2.5 bg-blue-800 text-white rounded-xl text-sm font-semibold hover:bg-blue-900">Save slide</button>
</div>
