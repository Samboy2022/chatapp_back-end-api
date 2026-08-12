@extends('layouts.admin')

@section('title', 'Edit slide')
@section('page-title', 'Edit Slide')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.sliders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to sliders
        </a>
    </div>

    <form method="POST" action="{{ route('admin.sliders.update', $slider) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.sliders._form')
    </form>
@endsection
