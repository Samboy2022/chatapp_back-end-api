@extends('layouts.admin')

@section('title', 'Import CBT questions')
@section('page-title', 'Import CBT Questions')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.cbt.questions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <i class="ph ph-arrow-left"></i> Back to questions
        </a>
    </div>

    @include('admin.cbt._flash')

    @if (session('import_errors'))
        <div class="mb-6 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-sm">
            <p class="font-semibold mb-1">Skipped rows: fix these and import them again</p>
            <ul class="list-disc list-inside space-y-0.5 max-h-64 overflow-y-auto">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-1">Upload a CSV</h3>
            <p class="text-sm text-gray-500 mb-4">
                One question per row. Save from Excel or Google Sheets as <strong>CSV (UTF-8)</strong>. New subjects and topics are
                created automatically.
            </p>
            <form method="POST" action="{{ route('admin.cbt.questions.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" required
                       class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-800 file:font-semibold">
                <button class="px-4 py-2.5 bg-blue-800 text-white rounded-xl text-sm font-semibold hover:bg-blue-900">
                    <i class="ph ph-upload-simple"></i> Import questions
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-3">Columns</h3>
            <a href="{{ route('admin.cbt.questions.template') }}" class="inline-flex items-center gap-2 mb-4 text-sm font-semibold text-blue-800 hover:underline">
                <i class="ph ph-download-simple"></i> Download the template
            </a>
            <ul class="text-sm text-gray-600 space-y-1.5">
                <li><code>subject</code>: required, e.g. Mathematics</li>
                <li><code>topic</code>: optional, e.g. Algebra</li>
                <li><code>exam_type</code>: jamb, waec, neco, or empty for any</li>
                <li><code>year</code>: past-paper year, e.g. 2021 (optional)</li>
                <li><code>question</code>: required</li>
                <li><code>option_a</code> … <code>option_e</code>: A and B required</li>
                <li><code>answer</code>: the correct letter, e.g. B</li>
                <li><code>explanation</code>: optional</li>
            </ul>
        </div>
    </div>
@endsection
