<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Category;
use App\Services\PromptGeneratorService;
use App\Services\AssetGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutomationController extends Controller
{
    protected $promptService;
    protected $assetService;

    public function __construct(PromptGeneratorService $promptService, AssetGeneratorService $assetService)
    {
        $this->promptService = $promptService;
        $this->assetService = $assetService;
    }

    /**
     * List all schedules
     */
    public function index()
    {
        $schedules = Schedule::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    /**
     * Create new schedule
     */
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
            'is_active' => 'boolean',
        ]);

        $schedule = Schedule::create($validated);

        return response()->json([
            'success' => true,
            'data' => $schedule->load('category'),
            'message' => 'Schedule created successfully',
        ], 201);
    }

    /**
     * Show single schedule
     */
    public function show(Schedule $schedule)
    {
        return response()->json([
            'success' => true,
            'data' => $schedule->load('category'),
        ]);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'schedule_type' => 'sometimes|in:daily,weekly,custom',
            'run_at' => 'nullable',
            'days' => 'nullable|array',
            'generate_prompts' => 'boolean',
            'generate_assets' => 'boolean',
            'asset_count' => 'integer|min:1|max:50',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'data' => $schedule->fresh()->load('category'),
            'message' => 'Schedule updated successfully',
        ]);
    }

    /**
     * Toggle schedule active status
     */
    public function toggle(Schedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        return response()->json([
            'success' => true,
            'data' => $schedule->fresh(),
            'message' => $schedule->is_active ? 'Schedule enabled' : 'Schedule disabled',
        ]);
    }

    /**
     * Delete schedule
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully',
        ]);
    }

    /**
     * Run schedule manually
     */
    public function run(Schedule $schedule)
    {
        $results = [];

        try {
            // Generate prompts if enabled
            if ($schedule->generate_prompts) {
                $category = $schedule->category;
                
                for ($i = 0; $i < 2; $i++) {
                    $result = $this->promptService->generateAuto($category->slug, 'en', 'image');
                    $result['category_id'] = $category->id;
                    $prompt = $this->promptService->createPrompt($result);
                    $results['prompts'][] = $prompt;
                }
            }

            // Generate assets if enabled
            if ($schedule->generate_assets) {
                $assets = $this->assetService->batchGenerate(
                    $schedule->category_id,
                    $schedule->asset_count ?? 5
                );
                $results['assets'] = $assets;
            }

            // Update last run time
            $schedule->update(['last_run_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Schedule executed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Schedule execution failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run all active schedules
     */
    public function runAll()
    {
        $schedules = Schedule::where('is_active', true)->get();
        $results = [];

        foreach ($schedules as $schedule) {
            if ($schedule->shouldRun()) {
                try {
                    $result = $this->runSchedule($schedule);
                    $results[$schedule->id] = $result;
                } catch (\Exception $e) {
                    Log::error("Schedule {$schedule->id} failed: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'All active schedules executed',
        ]);
    }

    /**
     * Internal method to run a single schedule
     */
    protected function runSchedule(Schedule $schedule): array
    {
        $results = [];
        
        if ($schedule->generate_prompts) {
            $category = $schedule->category;
            for ($i = 0; $i < 2; $i++) {
                $result = $this->promptService->generateAuto($category->slug, 'en', 'image');
                $result['category_id'] = $category->id;
                $results['prompts'][] = $this->promptService->createPrompt($result);
            }
        }

        if ($schedule->generate_assets) {
            $results['assets'] = $this->assetService->batchGenerate(
                $schedule->category_id,
                $schedule->asset_count ?? 5
            );
        }

        $schedule->update(['last_run_at' => now()]);

        return $results;
    }
}