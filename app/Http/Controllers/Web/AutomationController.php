<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Category;
use App\Services\PromptGeneratorService;
use App\Services\AssetGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    protected $promptService;
    protected $assetService;

    public function __construct(PromptGeneratorService $promptService, AssetGeneratorService $assetService)
    {
        $this->promptService = $promptService;
        $this->assetService = $assetService;
    }

    public function index(): View
    {
        $schedules = Schedule::with('category')->orderBy('created_at', 'desc')->get();
        return view('automation.index', compact('schedules'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        return view('automation.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'schedule_type' => 'required|in:daily,weekly,custom',
            'run_at' => 'nullable',
            'days' => 'nullable|array',
            'generate_prompts' => 'boolean',
            'generate_assets' => 'boolean',
            'asset_count' => 'integer|min:1|max:50',
        ]);

        Schedule::create($validated);
        return redirect()->route('automation.index')->with('success', 'Schedule created!');
    }

    public function run(Schedule $schedule)
    {
        try {
            $results = [];

            if ($schedule->generate_prompts) {
                $category = $schedule->category;
                for ($i = 0; $i < 2; $i++) {
                    $result = $this->promptService->generateAuto($category->slug, 'en', 'image');
                    $result['category_id'] = $category->id;
                    $this->promptService->createPrompt($result);
                }
                $results['prompts'] = 2;
            }

            if ($schedule->generate_assets) {
                $assets = $this->assetService->batchGenerate($schedule->category_id, $schedule->asset_count ?? 5);
                $results['assets'] = count($assets);
            }

            $schedule->update(['last_run_at' => now()]);

            return redirect()->back()->with('success', 'Schedule executed! ' . json_encode($results));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function toggle(Schedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);
        $status = $schedule->is_active ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Schedule {$status}!");
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('automation.index')->with('success', 'Schedule deleted!');
    }
}