@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Overview of your AI assets')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Assets</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ready to Upload</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['ready'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Images / Videos</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['images'] }} / {{ $stats['videos'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Schedules</p>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['schedules'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Generate Prompt -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Generate Prompt</h3>
        <form action="{{ route('prompts.generate') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 focus:ring-2 focus:ring-indigo-500">
                    <option value="bisnis">Bisnis</option>
                    <option value="teknologi">Teknologi</option>
                    <option value="lifestyle">Lifestyle</option>
                    <option value="alam">Alam</option>
                    <option value="pendidikan">Pendidikan</option>
                    <option value="kesehatan">Kesehatan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 focus:ring-2 focus:ring-indigo-500">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium transition-colors">
                Generate Prompt
            </button>
        </form>
    </div>

    <!-- Generate Image -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Batch Generate</h3>
        <form action="{{ route('assets.batch') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 focus:ring-2 focus:ring-indigo-500">
                    @foreach(App\Models\Category::where('is_active', true)->get() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Count</label>
                <input type="number" name="count" value="5" min="1" max="50" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-colors">
                Generate Assets
            </button>
        </form>
    </div>

    <!-- Export -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Quick Export</h3>
        <div class="space-y-3">
            <a href="{{ route('export.csv') }}" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                <span class="font-medium">Export CSV</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <a href="{{ route('export.json') }}" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                <span class="font-medium">Export JSON</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <a href="{{ route('export.zip') }}" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-700 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                <span class="font-medium">Export ZIP</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Recent Assets -->
<div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Recent Assets</h3>
        <a href="{{ route('assets.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
    </div>
    @if($recentAssets->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($recentAssets as $asset)
                <div class="aspect-square bg-gray-100 dark:bg-dark-700 rounded-lg overflow-hidden relative group">
                    @if($asset->file_type === 'image')
                        <img src="{{ asset('storage/' . $asset->file_path) }}" alt="{{ $asset->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-dark-600">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="text-white text-xs font-medium px-2 py-1 bg-{{ $asset->status === 'ready' ? 'green' : ($asset->status === 'uploaded' ? 'blue' : 'yellow') }}-600 rounded">
                            {{ $asset->status }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No assets yet. Generate some prompts to get started!</p>
    @endif
</div>
@endsection