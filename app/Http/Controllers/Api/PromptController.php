<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Prompt;
use App\Services\PromptGeneratorService;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    protected $promptService;

    public function __construct(PromptGeneratorService $promptService)
    {
        $this->promptService = $promptService;
    }

    /**
     * List all prompts with filters
     */
    public function index(Request $request)
    {
        $query = Prompt::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        $prompts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }

    /**
     * Generate auto prompt
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'language' => 'nullable|string|default:en',
            'type' => 'nullable|string|in:image,video|default:image',
        ]);

        $result = $this->promptService->generateAuto(
            $validated['category'],
            $validated['language'] ?? 'en',
            $validated['type'] ?? 'image'
        );

        // Find category ID
        $category = Category::where('slug', $validated['category'])
            ->orWhere('name', $validated['category'])
            ->first();

        $result['category_id'] = $category?->id;

        // Create prompt record
        $prompt = $this->promptService->createPrompt($result);

        return response()->json([
            'success' => true,
            'data' => $prompt->load('category'),
            'message' => 'Prompt generated successfully',
        ]);
    }

    /**
     * Generate multiple prompts
     */
    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'count' => 'nullable|integer|min:1|max:20',
            'language' => 'nullable|string|default:en',
            'type' => 'nullable|string|in:image,video|default:image',
        ]);

        $count = $validated['count'] ?? 5;
        $category = Category::where('slug', $validated['category'])
            ->orWhere('name', $validated['category'])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $prompts = [];
        for ($i = 0; $i < $count; $i++) {
            $result = $this->promptService->generateAuto(
                $category->slug,
                $validated['language'] ?? 'en',
                $validated['type'] ?? 'image'
            );
            $result['category_id'] = $category->id;
            $prompts[] = $this->promptService->createPrompt($result);
        }

        return response()->json([
            'success' => true,
            'data' => $prompts,
            'message' => "Generated {$count} prompts successfully",
        ]);
    }

    /**
     * Create manual prompt
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'language' => 'nullable|string|default:en',
            'type' => 'nullable|string|in:image,video|default:image',
        ]);

        $prompt = Prompt::create([
            'prompt' => $validated['prompt'],
            'category_id' => $validated['category_id'],
            'language' => $validated['language'] ?? 'en',
            'type' => $validated['type'] ?? 'image',
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'data' => $prompt->load('category'),
            'message' => 'Prompt created successfully',
        ], 201);
    }

    /**
     * Show single prompt
     */
    public function show(Prompt $prompt)
    {
        return response()->json([
            'success' => true,
            'data' => $prompt->load(['category', 'assets']),
        ]);
    }

    /**
     * Update prompt
     */
    public function update(Request $request, Prompt $prompt)
    {
        $validated = $request->validate([
            'prompt' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'language' => 'nullable|string',
            'type' => 'nullable|string|in:image,video',
            'status' => 'nullable|in:draft,generated,ready,uploaded',
        ]);

        $prompt->update($validated);

        return response()->json([
            'success' => true,
            'data' => $prompt->fresh()->load('category'),
            'message' => 'Prompt updated successfully',
        ]);
    }

    /**
     * Delete prompt
     */
    public function destroy(Prompt $prompt)
    {
        $prompt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prompt deleted successfully',
        ]);
    }

    /**
     * Get available categories for generation
     */
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => $this->promptService->getCategories(),
        ]);
    }
}