<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Prompt;
use App\Services\AssetGeneratorService;
use App\Services\VideoGeneratorService;
use App\Services\AdobeStockOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AssetController extends Controller
{
    protected $assetService;
    protected $videoService;

    public function __construct(AssetGeneratorService $assetService, VideoGeneratorService $videoService)
    {
        $this->assetService = $assetService;
        $this->videoService = $videoService;
    }

    /**
     * List all assets with filters
     */
    public function index(Request $request)
    {
        $query = Asset::with('category');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $assets = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    /**
     * Generate image from prompt
     */
    public function generateImage(Request $request)
    {
        $validated = $request->validate([
            'prompt_id' => 'required|exists:prompts,id',
        ]);

        $prompt = Prompt::findOrFail($validated['prompt_id']);
        $category = $prompt->category;

        try {
            $asset = $this->assetService->generateImage($prompt->id, $category->slug);
            $prompt->update(['status' => 'generated']);

            return response()->json([
                'success' => true,
                'data' => $asset->load('category'),
                'message' => 'Image generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch generate images
     */
    public function batchGenerate(Request $request)
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

            return response()->json([
                'success' => true,
                'data' => $assets,
                'message' => 'Generated ' . count($assets) . ' assets successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate video from prompt
     */
    public function generateVideo(Request $request)
    {
        $validated = $request->validate([
            'prompt_id' => 'required|exists:prompts,id',
        ]);

        try {
            $asset = $this->videoService->generateFromPrompt($validated['prompt_id']);
            Prompt::find($validated['prompt_id'])->update(['status' => 'generated']);

            return response()->json([
                'success' => true,
                'data' => $asset->load('category'),
                'message' => 'Video generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate video: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload existing file
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200',
            'category_id' => 'required|exists:categories,id',
            'prompt_id' => 'nullable|exists:prompts,id',
        ]);

        try {
            $file = $request->file('file');
            $fileType = $file->getMimeType() === 'video/mp4' || 
                        $file->getMimeType() === 'video/quicktime' ? 'video' : 'image';

            if ($fileType === 'image') {
                $asset = $this->assetService->uploadImage(
                    $file,
                    $validated['category_id'],
                    $validated['prompt_id'] ?? null
                );
            } else {
                // For video, save and create asset
                $category = Category::findOrFail($validated['category_id']);
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('assets', $fileName, 'public');

                $asset = Asset::create([
                    'prompt_id' => $validated['prompt_id'] ?? null,
                    'category_id' => $validated['category_id'],
                    'file_path' => $filePath,
                    'file_type' => 'video',
                    'file_name' => $fileName,
                    'status' => 'draft',
                ]);

                $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
                $asset->update($metadata);
            }

            return response()->json([
                'success' => true,
                'data' => $asset->load('category'),
                'message' => 'File uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single asset
     */
    public function show(Asset $asset)
    {
        return response()->json([
            'success' => true,
            'data' => $asset->load(['category', 'prompt']),
        ]);
    }

    /**
     * Update asset metadata
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|array',
            'file_name' => 'nullable|string',
            'status' => 'nullable|in:draft,ready,uploaded',
        ]);

        $asset->update($validated);

        return response()->json([
            'success' => true,
            'data' => $asset->fresh()->load('category'),
            'message' => 'Asset updated successfully',
        ]);
    }

    /**
     * Update asset status
     */
    public function updateStatus(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,ready,uploaded',
        ]);

        $asset->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $asset->fresh(),
            'message' => 'Status updated to ' . $validated['status'],
        ]);
    }

    /**
     * Optimize for Adobe Stock
     */
    public function optimize(Asset $asset)
    {
        try {
            $optimized = app(AdobeStockOptimizerService::class)->optimize($asset);

            return response()->json([
                'success' => true,
                'data' => $optimized->load('category'),
                'message' => 'Asset optimized for Adobe Stock',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get metadata
     */
    public function metadata(Asset $asset)
    {
        $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);

        return response()->json([
            'success' => true,
            'data' => [
                'asset' => $asset,
                'metadata' => $metadata,
            ],
        ]);
    }

    /**
     * Delete asset
     */
    public function destroy(Asset $asset)
    {
        // Delete physical file
        $filePath = storage_path('app/public/' . $asset->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully',
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function stats()
    {
        $total = Asset::count();
        $draft = Asset::where('status', 'draft')->count();
        $ready = Asset::where('status', 'ready')->count();
        $uploaded = Asset::where('status', 'uploaded')->count();
        
        $images = Asset::where('file_type', 'image')->count();
        $videos = Asset::where('file_type', 'video')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'by_status' => [
                    'draft' => $draft,
                    'ready' => $ready,
                    'uploaded' => $uploaded,
                ],
                'by_type' => [
                    'image' => $images,
                    'video' => $videos,
                ],
            ],
        ]);
    }
}