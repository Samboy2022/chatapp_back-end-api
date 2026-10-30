@extends('layouts.admin')

@section('title', 'Edit CBT slide')
@section('page-title', 'Edit CBT slide')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.cbt.slides.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to slides
        </a>
    </div>
    <form method="POST" action="{{ route('admin.cbt.slides.update', $slide) }}">
        @method('PUT')
        @include('admin.cbt.slides._form')
    </form>
@endsection
