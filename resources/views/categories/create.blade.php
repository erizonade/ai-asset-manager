@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<form action="{{ route('categories.store') }}" method="POST" class="max-w-2xl space-y-6">
    @csrf
    
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700" placeholder="e.g., Bisnis">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700" placeholder="Category description..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Keywords (comma separated)</label>
                <input type="text" name="keywords" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700" placeholder="business, office, corporate">
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Create</button>
        <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</a>
    </div>
</form>
@endsection