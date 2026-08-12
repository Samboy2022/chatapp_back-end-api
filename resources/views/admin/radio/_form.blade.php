@php
    /** @var \App\Models\RadioProgram|null $program */
    $program = $program ?? null;
    $isEdit = (bool) $program;
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
      action="{{ $isEdit ? route('admin.radio.update', $program) : route('admin.radio.store') }}"
      enctype="multipart/form-data"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <!-- Main details -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Programme details</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                       value="{{ old('title', $program->title ?? '') }}"
                       placeholder="e.g. The Morning Harvest"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Presenter / host</label>
                <input type="text" name="host"
                       value="{{ old('host', $program->host ?? '') }}"
                       placeholder="e.g. Aisha Bello"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="4"
                          placeholder="What this programme covers"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">{{ old('description', $program->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                <select name="type" id="type-select" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $program->type ?? 'program') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-500">
                    Setting this to <strong>Live Broadcast</strong> puts it on air and moves the previous live station to the archive.
                </p>
            </div>
        </div>

        <!-- Audio -->
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Audio</h2>

            @if ($isEdit && $program->audio_url)
                <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                    <i class="ph ph-waveform text-xl text-green-700"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500">Current source</p>
                        <p class="text-sm text-gray-800 truncate">{{ $program->audio_url }}</p>
                    </div>
                </div>
                @unless ($program->isLive())
                    <audio controls preload="none" src="{{ $program->audio_url }}" class="w-full"></audio>
                @endunless
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload audio file</label>
                <input type="file" name="audio_file" accept="audio/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-green-50 file:text-green-700 file:text-sm file:font-medium hover:file:bg-green-100">
                <p class="mt-1.5 text-xs text-gray-500">
                    MP3, M4A, AAC, WAV or OGG. Up to 200&nbsp;MB. Used for programmes and archive recordings.
                </p>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                <div class="relative flex justify-center"><span class="px-2 bg-white text-xs text-gray-400">or</span></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream URL</label>
                <input type="url" name="stream_url"
                       value="{{ old('stream_url', $isEdit && !$program->audio_path ? $program->audio_url : '') }}"
                       placeholder="https://stream.example.com/live"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600">
                <p class="mt-1.5 text-xs text-gray-500">
                    Use this for the live station — an Icecast/Shoutcast mount or an HLS (.m3u8) URL.
                    An uploaded file takes priority if you supply both.
                </p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Artwork</h2>

            @if ($isEdit && $program->thumbnail_url)
                <img src="{{ $program->thumbnail_url }}" alt=""
                     class="w-full aspect-square object-cover rounded-xl border border-gray-100">
            @else
                <div class="w-full aspect-square rounded-xl bg-green-50 flex items-center justify-center">
                    <i class="ph ph-image text-4xl text-green-700/40"></i>
                </div>
            @endif

            <input type="file" name="thumbnail" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-green-50 file:text-green-700 file:text-sm file:font-medium hover:file:bg-green-100">
            <p class="text-xs text-gray-500">Square JPG, PNG or WebP up to 5&nbsp;MB. Shown as the programme tile in the app.</p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
            <h2 class="font-semibold text-gray-900">Publishing</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (seconds)</label>
                <input type="number" name="duration_seconds" min="0"
                       value="{{ old('duration_seconds', $program->duration_seconds ?? '') }}"
                       placeholder="Leave empty for live"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort order</label>
                <input type="number" name="sort_order"
                       value="{{ old('sort_order', $program->sort_order ?? 0) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                <p class="mt-1.5 text-xs text-gray-500">Lower numbers appear first.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Publish at</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', optional($program->published_at ?? now())->format('Y-m-d\TH:i')) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
                <p class="mt-1.5 text-xs text-gray-500">A future date hides it from the app until then.</p>
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $program->is_active ?? true))
                       class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-200">
                <span class="text-sm text-gray-700">Visible in the app</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_downloadable" value="0">
                <input type="checkbox" name="is_downloadable" value="1"
                       @checked(old('is_downloadable', $program->is_downloadable ?? true))
                       class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-200">
                <span class="text-sm text-gray-700">Listeners can download for offline</span>
            </label>
            <p class="text-xs text-gray-500 -mt-2">The live broadcast is never downloadable, whatever this is set to.</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.radio.index') }}"
               class="flex-1 text-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-300 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
                {{ $isEdit ? 'Save changes' : 'Create programme' }}
            </button>
        </div>
    </div>
</form>
