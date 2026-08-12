@php
    /** @var \App\Models\TvChannel|null $channel */
    $channel = $channel ?? null;
    $isEdit = (bool) $channel;
@endphp

@if ($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('admin.tv-channels.update', $channel) : route('admin.tv-channels.store') }}"
      enctype="multipart/form-data"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Channel details</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                       value="{{ old('title', $channel->title ?? '') }}"
                       placeholder="e.g. Arefan TV"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">{{ old('description', $channel->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream / channel URL <span class="text-red-500">*</span></label>
                <input type="url" name="stream_url" required
                       value="{{ old('stream_url', $channel->stream_url ?? '') }}"
                       placeholder="https://www.youtube.com/@arefantv"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">
                <p class="mt-1.5 text-xs text-gray-500">A YouTube channel/video URL or an HLS (.m3u8) stream. Opened in the app's built-in browser.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Artwork</h2>

            @if ($isEdit && $channel->thumbnail_url)
                <img src="{{ $channel->thumbnail_url }}" alt="" class="w-full aspect-video object-cover rounded-xl border border-gray-100">
            @else
                <div class="w-full aspect-video rounded-xl bg-green-50 flex items-center justify-center">
                    <i class="ph ph-image text-4xl text-green-700/40"></i>
                </div>
            @endif

            <input type="file" name="thumbnail" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-green-50 file:text-green-700 file:text-sm file:font-medium hover:file:bg-green-100">
            <p class="text-xs text-gray-500">16:9 JPG, PNG or WebP up to 5&nbsp;MB.</p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Publishing</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort order</label>
                <input type="number" name="sort_order"
                       value="{{ old('sort_order', $channel->sort_order ?? 0) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_live" value="0">
                <input type="checkbox" name="is_live" value="1"
                       @checked(old('is_live', $channel->is_live ?? false))
                       class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-200">
                <span class="text-sm text-gray-700">Currently broadcasting live</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $channel->is_active ?? true))
                       class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-200">
                <span class="text-sm text-gray-700">Visible in the app</span>
            </label>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.tv-channels.index') }}"
               class="flex-1 text-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-300 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
                {{ $isEdit ? 'Save changes' : 'Create channel' }}
            </button>
        </div>
    </div>
</form>
