@extends('layouts.app')

@section('title', 'Categories')
@section('subtitle', 'Manage content categories')

@section('content')
<!-- Actions -->
<div class="flex gap-4 mb-6">
    <a href="{{ route('categories.create') }}" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Category
    </a>
</div>

<!-- Categories Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($categories as $category)
        <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">{{ $category->name }}</h3>
                <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800' }}">
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $category->description }}</p>
            <div class="flex gap-4 text-sm">
                <span class="text-gray-500">Prompts: {{ $category->prompts_count }}</span>
                <span class="text-gray-500">Assets: {{ $category->assets_count }}</span>
            </div>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('categories.show', $category) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">View</a>
                <a href="{{ route('categories.edit', $category) }}" class="text-gray-600 dark:text-gray-400 hover:underline text-sm">Edit</a>
                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
            No categories yet.
        </div>
    @endforelse
</div>
@endsection