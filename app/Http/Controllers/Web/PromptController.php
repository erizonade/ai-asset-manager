<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Prompt;
use App\Services\PromptGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromptController extends Controller
{
    protected $promptService;

    public function __construct(PromptGeneratorService $promptService)
    {
        $this->promptService = $promptService;
    }

    public function index(Request $request): View
    {
        $query = Prompt::with('category');

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $prompts = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::all();

        return view('prompts.index', compact('prompts', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        return view('prompts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'type' => 'nullable|in:image,video',
        ]);

        Prompt::create([
            'prompt' => $validated['prompt'],
            'category_id' => $validated['category_id'],
            'type' => $validated['type'] ?? 'image',
            'language' => 'en',
            'status' => 'draft',
        ]);

        return redirect()->route('prompts.index')->with('success', 'Prompt created!');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'type' => 'nullable|in:image,video',
        ]);

        try {
            $result = $this->promptService->generateAuto(
                $validated['category'],
                'en',
                $validated['type'] ?? 'image'
            );

            $category = Category::where('slug', $validated['category'])
                ->orWhere('name', $validated['category'])
                ->first();

            $result['category_id'] = $category?->id;
            $this->promptService->createPrompt($result);

            return redirect()->route('prompts.index')->with('success', 'Prompt generated!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate prompt: ' . $e->getMessage());
        }
    }

    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'count' => 'nullable|integer|min:1|max:20',
            'type' => 'nullable|in:image,video',
        ]);

        try {
            $category = Category::where('slug', $validated['category'])
                ->orWhere('name', $validated['category'])
                ->first();

            if (!$category) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $count = $validated['count'] ?? 5;
            for ($i = 0; $i < $count; $i++) {
                $result = $this->promptService->generateAuto($category->slug, 'en', $validated['type'] ?? 'image');
                $result['category_id'] = $category->id;
                $this->promptService->createPrompt($result);
            }

            return redirect()->route('prompts.index')->with('success', "Generated {$count} prompts!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show(Prompt $prompt): View
    {
        $prompt->load('category');
        return view('prompts.show', compact('prompt'));
    }

    public function update(Request $request, Prompt $prompt)
    {
        $validated = $request->validate([
            'prompt' => 'sometimes|string',
            'type' => 'nullable|in:image,video',
            'status' => 'nullable|in:draft,generated,ready,uploaded',
        ]);

        $prompt->update($validated);
        return redirect()->back()->with('success', 'Prompt updated!');
    }

    public function destroy(Prompt $prompt)
    {
        $prompt->delete();
        return redirect()->route('prompts.index')->with('success', 'Prompt deleted!');
    }
}