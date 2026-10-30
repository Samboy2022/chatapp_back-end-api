@extends('layouts.admin')

@section('title', 'New CBT question')
@section('page-title', 'New CBT Question')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.cbt.questions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to questions
        </a>
    </div>
    <form method="POST" action="{{ route('admin.cbt.questions.store') }}">
        @include('admin.cbt.questions._form')
    </form>
@endsection
