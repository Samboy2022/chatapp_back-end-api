@extends('layouts.admin')

@section('title', 'Sliders')
@section('page-title', 'Features Screen Sliders')

@section('content')
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <i class="ph ph-warning-circle text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-6 rounded-2xl p-5 bg-gradient-to-br from-green-700 to-green-900 text-white shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                <i class="ph ph-images text-3xl"></i>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-semibold">Slides on the app's Features screen</p>
                <p class="text-white/70 text-sm">
                    @if (filter_var($config['enabled'], FILTER_VALIDATE_BOOLEAN))
                        Showing {{ $stats['active'] }} slide{{ $stats['active'] === 1 ? '' : 's' }},
                        {{ $config['autoplay_seconds'] }}s each, {{ $config['height'] }}px tall by default.
                    @else
                        The slider is switched off — no slides are shown in the app.
                    @endif
                </p>
            </div>

            <a href="{{ route('admin.settings.index') }}"
               class="shrink-0 hidden sm:flex items-center gap-2 px-4 py-2.5 bg-white/15 text-white rounded-xl text-sm font-semibold hover:bg-white/25 transition-colors">
                <i class="ph ph-sliders-horizontal text-lg"></i>
                <span>Slider settings</span>
            </a>

            <a href="{{ route('admin.sliders.create') }}"
               class="shrink-0 flex items-center gap-2 px-4 py-2.5 bg-white text-green-800 rounded-xl text-sm font-semibold hover:bg-green-50 transition-colors">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">New slide</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach ([['Total', $stats['total'], 'ph-images', 'blue'], ['Visible', $stats['active'], 'ph-eye', 'green'], ['Hidden', $stats['hidden'], 'ph-eye-slash', 'gray']] as [$label, $value, $icon, $color])
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $color }}-50 flex items-center justify-center">
                        <i class="ph {{ $icon }} text-{{ $color }}-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 leading-none">{{ $value }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($sliders->isEmpty())
        <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
            <i class="ph ph-images text-5xl text-gray-300"></i>
            <p class="mt-4 font-semibold text-gray-900">No slides yet</p>
            <p class="text-sm text-gray-500 mt-1">Add your first slide to show it on the app's Features screen.</p>
            <a href="{{ route('admin.sliders.create') }}"
               class="inline-flex items-center gap-2 mt-6 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
                <i class="ph ph-plus text-lg"></i> New slide
            </a>
        </div>
    @else
        <p class="text-xs text-gray-500 mb-3 flex items-center gap-1.5">
            <i class="ph ph-dots-six-vertical"></i>
            Drag a slide to change the order it appears in the app.
        </p>

        <div id="sliderList" class="space-y-3">
            @foreach ($sliders as $slider)
                <div class="slider-row bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex items-center gap-4"
                     data-id="{{ $slider->id }}" draggable="true">

                    <i class="ph ph-dots-six-vertical text-gray-300 text-xl cursor-grab shrink-0"></i>

                    {{-- Thumbnail rendered with the slide's own fit, so the list
                         previews what the app will actually show. --}}
                    <div class="w-32 h-20 rounded-xl bg-gray-100 overflow-hidden shrink-0">
                        @if ($slider->resolved_image_url)
                            <img src="{{ $slider->resolved_image_url }}" alt=""
                                 class="w-full h-full"
                                 style="object-fit: {{ $slider->image_fit ?: 'cover' }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="ph ph-image text-gray-300 text-2xl"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-900 truncate">
                                {{ $slider->title ?: 'Untitled slide' }}
                            </p>
                            @if (!$slider->is_active)
                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[10px] font-bold">HIDDEN</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 truncate">{{ $slider->subtitle ?: '—' }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $slider->effectiveHeight() }}px · {{ ucfirst($slider->image_fit ?: 'cover') }}
                            @if ($slider->link_url)
                                · links out
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('admin.sliders.toggle-active', $slider) }}">
                            @csrf
                            <button type="submit"
                                    title="{{ $slider->is_active ? 'Hide from the app' : 'Show in the app' }}"
                                    class="w-9 h-9 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center">
                                <i class="ph {{ $slider->is_active ? 'ph-eye' : 'ph-eye-slash' }}"></i>
                            </button>
                        </form>

                        <a href="{{ route('admin.sliders.edit', $slider) }}"
                           class="w-9 h-9 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center">
                            <i class="ph ph-pencil-simple"></i>
                        </a>

                        <form method="POST" action="{{ route('admin.sliders.destroy', $slider) }}"
                              onsubmit="return confirm('Delete this slide? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-9 h-9 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 flex items-center justify-center">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
<script>
// Drag-to-reorder. Plain HTML5 drag events rather than a library — this is the
// only sortable list in the dashboard and it isn't worth a dependency.
(function () {
    const list = document.getElementById('sliderList');
    if (!list) return;

    let dragged = null;

    list.addEventListener('dragstart', (e) => {
        dragged = e.target.closest('.slider-row');
        if (dragged) dragged.classList.add('opacity-40');
    });

    list.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('opacity-40');
        dragged = null;
        persistOrder();
    });

    list.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.target.closest('.slider-row');
        if (!target || !dragged || target === dragged) return;

        // Insert before or after depending on which half of the row we're over,
        // so the drop lands where the cursor actually is.
        const box = target.getBoundingClientRect();
        const after = (e.clientY - box.top) > box.height / 2;
        list.insertBefore(dragged, after ? target.nextSibling : target);
    });

    function persistOrder() {
        const ids = [...list.querySelectorAll('.slider-row')].map((row) => Number(row.dataset.id));

        fetch('{{ route('admin.sliders.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids }),
        }).catch(() => {
            // Order is cosmetic until it saves; a reload shows the truth.
            alert('Could not save the new order. Please reload and try again.');
        });
    }
})();
</script>
@endpush
