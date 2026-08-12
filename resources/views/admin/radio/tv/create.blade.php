@extends('layouts.admin')

@section('title', 'New TV Channel')
@section('page-title', 'New TV Channel')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.tv-channels.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 transition-colors">
            <i class="ph ph-arrow-left"></i> Back to TV Channels
        </a>
    </div>

    @include('admin.radio.tv._form', ['channel' => null])
@endsection
