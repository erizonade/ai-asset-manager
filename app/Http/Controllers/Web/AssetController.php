<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Prompt;
use App\Services\AssetGeneratorService;
use App\Services\VideoGeneratorService;
use App\Services\AdobeStockOptimizerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    protected $assetService;
    protected $videoService;

    public function __construct(AssetGeneratorService $assetService, VideoGeneratorService $videoService)
    {
        $this->assetService = $assetService;
        $this->videoService = $videoService;
    }

    public function index(Request $request): View
    {
        $query = Asset::with('category');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('file_type', $request->type);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::all();

        return view('assets.index', compact('assets', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        $prompts = Prompt::where('status', 'draft')->get();
        return view('assets.create', compact('categories', 'prompts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prompt_id' => 'required|exists:prompts,id',
        ]);

        try {
            $prompt = Prompt::findOrFail($validated['prompt_id']);
            $category = $prompt->category;
            
            $asset = $this->assetService->generateImage($prompt->id, $category->slug);
            $prompt->update(['status' => 'generated']);

            return redirect()->route('assets.index')->with('success', 'Asset generated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate asset: ' . $e->getMessage());
        }
    }

    public function storeVideo(Request $request)
    {
        $validated = $request->validate([
            'prompt_id' => 'required|exists:prompts,id',
        ]);

        try {
            $asset = $this->videoService->generateFromPrompt($validated['prompt_id']);
            Prompt::find($validated['prompt_id'])->update(['status' => 'generated']);

            return redirect()->route('assets.index')->with('success', 'Video generated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate video: ' . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'count' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $assets = $this->assetService->batchGenerate(
                $validated['category_id'],
                $validated['count'] ?? 5
            );

            // Get fresh data for view
            $assetIds = collect($assets)->pluck('id');
            $generatedAssets = Asset::with('category')->whereIn('id', $assetIds)->get();
            $categories = Category::all();

            return view('assets.index', [
                'assets' => $generatedAssets,
                'categories' => $categories,
                'generated' => $generatedAssets,
                'success' => 'Generated ' . count($assets) . ' assets successfully!'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Batch generation failed: ' . $e->getMessage());
        }
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            $file = $request->file('file');
            $fileType = in_array($file->getMimeType(), ['video/mp4', 'video/quicktime']) ? 'video' : 'image';

            if ($fileType === 'image') {
                $asset = $this->assetService->uploadImage($file, $validated['category_id']);
            } else {
                $category = Category::findOrFail($validated['category_id']);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('assets', $fileName, 'public');

                $asset = Asset::create([
                    'category_id' => $validated['category_id'],
                    'file_path' => $filePath,
                    'file_type' => 'video',
                    'file_name' => $fileName,
                    'status' => 'draft',
                ]);

                $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
                $asset->update($metadata);
            }

            return redirect()->route('assets.index')->with('success', 'File uploaded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'prompt']);
        return view('assets.show', compact('asset'));
    }

    public function updateStatus(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,ready,uploaded',
        ]);

        $asset->update(['status' => $validated['status']]);
        return redirect()->back()->with('success', 'Status updated!');
    }

    public function optimize(Asset $asset)
    {
        try {
            $optimized = app(AdobeStockOptimizerService::class)->optimize($asset);
            return redirect()->back()->with('success', 'Asset optimized for Adobe Stock!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Optimization failed: ' . $e->getMessage());
        }
    }

    public function upscale(Asset $asset)
    {
        try {
            $upscaled = app(AssetGeneratorService::class)->upscale($asset);
            return redirect()->back()->with('success', 'Image upscaled to 4K!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Upscale failed: ' . $e->getMessage());
        }
    }

    public function destroy(Asset $asset)
    {
        $filePath = storage_path('app/public/' . $asset->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted!');
    }
}