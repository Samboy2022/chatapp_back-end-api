@extends('layouts.admin')

@section('title', 'New slide')
@section('page-title', 'New Slide')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.sliders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to sliders
        </a>
    </div>

    {{-- enctype is required for the image upload; without it the file never
         reaches the server and validation reports a missing image. --}}
    <form method="POST" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">
        @include('admin.sliders._form')
    </form>
@endsection
