@extends('layouts.app')

@section('title', 'Prompts')
@section('subtitle', 'Manage your AI prompts')

@section('content')
<!-- Actions -->
<div class="flex gap-4 mb-6">
    <button onclick="document.getElementById('generateModal').classList.remove('hidden')" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Generate Prompt
    </button>
    <button onclick="document.getElementById('manualModal').classList.remove('hidden')" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Manual Prompt
    </button>
</div>

<!-- Generate Modal -->
<div id="generateModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Generate Prompt</h3>
        <form action="{{ route('prompts.generate') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select name="category" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
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
                <select name="type" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium">Generate</button>
                <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Manual Modal -->
<div id="manualModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-800 rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Create Manual Prompt</h3>
        <form action="{{ route('prompts.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Prompt</label>
                <textarea name="prompt" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700" placeholder="Enter your custom prompt..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <select name="category_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Type</label>
                    <select name="type" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg font-medium">Create</button>
                <button type="button" onclick="document.getElementById('manualModal').classList.add('hidden')" class="px-6 py-2 bg-gray-300 dark:bg-dark-600 rounded-lg font-medium">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Prompts List -->
<div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-dark-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prompt</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
            @forelse($prompts as $prompt)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50">
                    <td class="px-6 py-4">
                        <p class="text-sm line-clamp-2">{{ $prompt->prompt }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $prompt->category->name ?? 'Unknown' }}</td>
                    <td class="px-6 py-4 text-sm capitalize">{{ $prompt->type }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $prompt->status === 'generated' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($prompt->status === 'ready' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                            {{ $prompt->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <form action="{{ route('prompts.generate') }}" method="POST">
                                @csrf
                                <input type="hidden" name="category" value="{{ $prompt->category->slug ?? 'bisnis' }}">
                                <input type="hidden" name="type" value="{{ $prompt->type }}">
                                <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Generate Asset</button>
                            </form>
                            <form action="{{ route('prompts.destroy', $prompt) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        No prompts yet. Generate some!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $prompts->links() }}
</div>
@endsection