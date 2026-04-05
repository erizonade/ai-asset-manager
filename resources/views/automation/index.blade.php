@extends('layouts.app')

@section('title', 'Automation')
@section('subtitle', 'Manage your scheduled tasks')

@section('content')
<!-- Actions -->
<div class="flex gap-4 mb-6">
    <a href="{{ route('automation.create') }}" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Schedule
    </a>
</div>

<!-- Schedules -->
<div class="space-y-4">
    @forelse($schedules as $schedule)
        <div class="bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 dark:border-dark-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-lg">{{ $schedule->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $schedule->category->name ?? 'Unknown' }} • 
                        {{ ucfirst($schedule->schedule_type) }} • 
                        {{ $schedule->run_at ? 'at ' . $schedule->run_at->format('H:i') : 'custom' }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $schedule->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="flex gap-2">
                        <form action="{{ route('automation.run', $schedule) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Run Now</button>
                        </form>
                        <form action="{{ route('automation.toggle', $schedule) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 {{ $schedule->is_active ? 'bg-gray-600' : 'bg-green-600' }} hover:opacity-80 text-white rounded-lg text-sm font-medium">
                                {{ $schedule->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form action="{{ route('automation.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Delete this schedule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span>Prompts: {{ $schedule->generate_prompts ? 'Yes' : 'No' }}</span>
                <span>Assets: {{ $schedule->generate_assets ? 'Yes' : 'No' }}</span>
                @if($schedule->last_run_at)
                    <span>Last run: {{ $schedule->last_run_at->format('M d, Y H:i') }}</span>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-dark-800 rounded-xl p-12 border border-gray-200 dark:border-dark-700 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">No schedules yet. Create one to automate your workflow!</p>
            <a href="{{ route('automation.create') }}" class="inline-block mt-4 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Create Schedule</a>
        </div>
    @endforelse
</div>
@endsection