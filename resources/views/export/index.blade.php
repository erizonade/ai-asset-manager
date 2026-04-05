@extends('layouts.app')

@section('title', 'Export')
@section('subtitle', 'Export assets for Adobe Stock')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Ready Assets</p>
        <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Images</p>
        <p class="text-3xl font-bold">{{ $stats['images'] }}</p>
    </div>
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Videos</p>
        <p class="text-3xl font-bold">{{ $stats['videos'] }}</p>
    </div>
</div>

<!-- Export Options -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- CSV -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">CSV Export</h3>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Export metadata as CSV file for bulk upload to Adobe Stock.</p>
        <a href="{{ route('export.csv') }}" class="block w-full text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">Download CSV</a>
    </div>

    <!-- JSON -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">JSON Export</h3>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Export metadata as JSON for integration with other tools.</p>
        <a href="{{ route('export.json') }}" class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Download JSON</a>
    </div>

    <!-- ZIP -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">ZIP Package</h3>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Download all assets with metadata JSON files included.</p>
        <a href="{{ route('export.zip') }}" class="block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium">Download ZIP</a>
    </div>
</div>

<!-- By Category -->
@if($categories->count() > 0)
<div class="mt-8 bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
    <h3 class="text-lg font-semibold mb-4">By Category</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($categories as $name => $count)
            <div class="p-4 bg-gray-50 dark:bg-dark-700 rounded-lg text-center">
                <p class="font-medium">{{ $name }}</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $count }}</p>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection