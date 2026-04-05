@extends('layouts.app')

@section('title', 'Asset Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Preview -->
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Preview</h3>
        <div class="aspect-video bg-gray-100 dark:bg-dark-700 rounded-lg overflow-hidden">
            @if($asset->file_type === 'image')
                <img src="{{ asset('storage/' . $asset->file_path) }}" alt="{{ $asset->title }}" class="w-full h-full object-contain">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
    </div>

    <!-- Details -->
    <div class="space-y-6">
        <!-- Status -->
        <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
            <h3 class="text-lg font-semibold mb-4">Status</h3>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 rounded-full font-medium 
                    {{ $asset->status === 'ready' ? 'bg-green-600 text-white' : ($asset->status === 'uploaded' ? 'bg-blue-600 text-white' : 'bg-yellow-600 text-white') }}">
                    {{ ucfirst($asset->status) }}
                </span>
                <form action="{{ route('assets.status', $asset) }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="status" class="px-3 py-1 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm">
                        <option value="draft" {{ $asset->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="ready" {{ $asset->status === 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="uploaded" {{ $asset->status === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                    </select>
                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm">Update</button>
                </form>
            </div>
        </div>

        <!-- Metadata -->
        <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
            <h3 class="text-lg font-semibold mb-4">Adobe Stock Metadata</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Title</label>
                    <p class="mt-1">{{ $asset->title ?? 'Not set' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <p class="mt-1 text-sm">{{ $asset->description ?? 'Not set' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Keywords ({{ count($asset->keywords ?? []) }})</label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @forelse($asset->keywords ?? [] as $keyword)
                            <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded text-xs">{{ $keyword }}</span>
                        @empty
                            <span class="text-sm text-gray-500">No keywords</span>
                        @endforelse
                    </div>
                </div>
                <form action="{{ route('assets.optimize', $asset) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">
                        Optimize for Adobe Stock
                    </button>
                </form>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
            <h3 class="text-lg font-semibold mb-4">Information</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Category</dt>
                    <dd class="font-medium">{{ $asset->category->name ?? 'Unknown' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Type</dt>
                    <dd class="font-medium capitalize">{{ $asset->file_type }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">File Name</dt>
                    <dd class="font-medium text-sm">{{ $asset->file_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="font-medium">{{ $asset->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="mt-6 flex gap-4">
    <a href="{{ route('assets.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Back</a>
    <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Are you sure?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">Delete</button>
    </form>
</div>
@endsection