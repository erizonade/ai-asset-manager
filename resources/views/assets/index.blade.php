@extends('layouts.app')

@section('title', 'Assets')
@section('subtitle', 'Manage your images and videos')

@section('content')
<!-- Filters -->
<div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <select name="status" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>Uploaded</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Type</label>
            <select name="type" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                <option value="">All Types</option>
                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Image</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Category</label>
            <select name="category_id" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
            Filter
        </button>
    </form>
</div>

<!-- Actions -->
<div class="flex gap-4 mb-6">
    <button onclick="document.getElementById('generateModal').classList.remove('hidden')" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Generate Assets
    </button>
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Upload File
    </button>
</div>

<!-- Generate Modal -->
<div id="generateModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Generate Assets</h3>
        <form action="{{ route('assets.batch') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Number of Assets</label>
                <input type="number" name="count" value="5" min="1" max="50" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium">Generate</button>
                <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Upload File</h3>
        <form action="{{ route('assets.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">File</label>
                <input type="file" name="file" accept="image/*,video/*" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">Upload</button>
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Assets Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($assets as $asset)
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden group">
            <div class="aspect-square bg-gray-100 dark:bg-dark-700 relative">
                @if($asset->file_type === 'image')
                    <img src="{{ asset('storage/' . $asset->file_path) }}" alt="{{ $asset->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
                <div class="absolute top-2 right-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        {{ $asset->status === 'ready' ? 'bg-green-600 text-white' : ($asset->status === 'uploaded' ? 'bg-blue-600 text-white' : 'bg-yellow-600 text-white') }}">
                        {{ $asset->status }}
                    </span>
                </div>
            </div>
            <div class="p-3">
                <p class="font-medium text-sm truncate">{{ $asset->title ?? 'Untitled' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->category->name ?? 'Unknown' }}</p>
                <div class="flex gap-2 mt-2">
                    <a href="{{ route('assets.show', $asset) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                    <form action="{{ route('assets.status', $asset) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="{{ $asset->status === 'draft' ? 'ready' : 'uploaded' }}">
                        <button type="submit" class="text-xs text-green-600 hover:underline">{{ $asset->status === 'draft' ? 'Mark Ready' : 'Mark Uploaded' }}</button>
                    </form>
                    <form action="{{ route('assets.optimize', $asset) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-blue-600 hover:underline">Optimize</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
            No assets found. Generate some prompts first!
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $assets->links() }}
</div>
@endsection