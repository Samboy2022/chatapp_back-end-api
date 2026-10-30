@extends('layouts.admin')

@section('title', 'CBT Slides')
@section('page-title', 'CBT Practice Slides')

@section('content')
    @include('admin.cbt._flash')

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-gray-500">Banners at the top of the app's CBT Practice screen. The app shows up to five visible slides, in this order.</p>
        <a href="{{ route('admin.cbt.slides.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-blue-800 text-white rounded-xl text-sm font-semibold hover:bg-blue-900">
            <i class="ph ph-plus text-lg"></i> New slide
        </a>
    </div>

    @if ($slides->isEmpty())
        <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
            <i class="ph ph-slideshow text-5xl text-gray-300"></i>
            <p class="mt-4 font-semibold text-gray-900">No slides yet</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($slides as $slide)
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden {{ $slide->is_active ? '' : 'opacity-60' }}">
                    <div class="p-5 text-white" style="background: linear-gradient(135deg, {{ $slide->color_from }}, {{ $slide->color_to }})">
                        @if ($slide->badge)
                            <span class="inline-block px-2.5 py-1 rounded-full border border-white/40 bg-white/10 text-[11px] font-semibold tracking-wide">{{ $slide->badge }}</span>
                        @endif
                        <p class="mt-3 text-xl font-bold">{{ $slide->title }}</p>
                        <p class="mt-1 text-sm text-white/80">{{ $slide->subtitle }}</p>
                        @if ($slide->cta_label)
                            <span class="inline-block mt-4 px-4 py-2 rounded-full bg-white text-gray-900 text-sm font-semibold">{{ $slide->cta_label }}</span>
                        @endif
                    </div>
                    <div class="px-4 py-3 flex items-center justify-between text-sm">
                        <span class="text-gray-500">
                            #{{ $slide->sort_order }} · {{ $slide->is_active ? 'Visible' : 'Hidden' }}
                            @if ($slide->cta_target) · opens {{ $slide->cta_target }} @endif
                        </span>
                        <div class="flex items-center">
                            <form method="POST" action="{{ route('admin.cbt.slides.toggle-active', $slide) }}">
                                @csrf
                                <button class="p-2 text-gray-500 hover:text-blue-700" title="{{ $slide->is_active ? 'Hide' : 'Show' }}">
                                    <i class="ph {{ $slide->is_active ? 'ph-eye-slash' : 'ph-eye' }} text-lg"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.cbt.slides.edit', $slide) }}" class="p-2 text-gray-500 hover:text-blue-700" title="Edit">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.cbt.slides.destroy', $slide) }}" onsubmit="return confirm('Delete this slide?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-500 hover:text-red-600" title="Delete"><i class="ph ph-trash text-lg"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
