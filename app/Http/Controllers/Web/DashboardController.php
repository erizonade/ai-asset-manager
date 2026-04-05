<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => \App\Models\Asset::count(),
            'draft' => \App\Models\Asset::where('status', 'draft')->count(),
            'ready' => \App\Models\Asset::where('status', 'ready')->count(),
            'uploaded' => \App\Models\Asset::where('status', 'uploaded')->count(),
            'images' => \App\Models\Asset::where('file_type', 'image')->count(),
            'videos' => \App\Models\Asset::where('file_type', 'video')->count(),
            'prompts' => \App\Models\Prompt::count(),
            'schedules' => \App\Models\Schedule::where('is_active', true)->count(),
        ];

        $recentAssets = \App\Models\Asset::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'recentAssets'));
    }
}