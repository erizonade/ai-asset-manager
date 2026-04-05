@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<form action="{{ route('categories.update', $category) }}" method="POST" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')
    
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" value="{{ $category->name }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">{{ $category->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Keywords (comma separated)</label>
                <input type="text" name="keywords" value="{{ implode(', ', $category->keywords ?? []) }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} class="w-5 h-5 rounded">
                <span>Active</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Update</button>
        <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</a>
    </div>
</form>
@endsection