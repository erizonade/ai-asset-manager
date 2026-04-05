@extends('layouts.app')

@section('title', 'Create Schedule')
@section('subtitle', 'Set up automated asset generation')

@section('content')
<form action="{{ route('automation.store') }}" method="POST" class="max-w-2xl space-y-6">
    @csrf
    
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700" placeholder="e.g., Daily Business Prompts">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category_id" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Schedule</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Type</label>
                <select name="schedule_type" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Run At (Time)</label>
                <input type="time" name="run_at" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="space-y-4">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="generate_prompts" value="1" checked class="w-5 h-5 rounded border-gray-300 dark:border-dark-600">
                <span>Generate Prompts</span>
            </label>
            <label class="flex items-center gap-3">
                <input type="checkbox" name="generate_assets" value="1" class="w-5 h-5 rounded border-gray-300 dark:border-dark-600">
                <span>Generate Assets</span>
            </label>
            <div>
                <label class="block text-sm font-medium mb-2">Asset Count (if generating assets)</label>
                <input type="number" name="asset_count" value="5" min="1" max="50" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Create Schedule</button>
        <a href="{{ route('automation.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</a>
    </div>
</form>
@endsection