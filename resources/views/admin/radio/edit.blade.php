@extends('layouts.admin')

@section('title', 'Edit Radio Programme')
@section('page-title', 'Edit Radio Programme')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.radio.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 transition-colors">
            <i class="ph ph-arrow-left"></i> Back to Radio
        </a>
    </div>

    @include('admin.radio._form', ['program' => $program, 'types' => $types])
@endsection
