<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Prompt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AssetGeneratorService
{
    protected string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/public/assets');
        
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Generate mock image (placeholder) - replace with actual AI API in production
     */
    public function generateImage(int $promptId, string $categorySlug): Asset
    {
        $prompt = Prompt::findOrFail($promptId);
        
        // Create mock image - in production, integrate with DALL-E/Stable Diffusion API
        $imageData = $this->createMockImage($prompt->prompt);
        
        // Generate SEO-friendly filename
        $fileName = $this->generateSeoFileName($categorySlug, $prompt->id, 'jpg');
        $filePath = "assets/{$fileName}";
        
        // Save to storage
        $fullPath = storage_path("app/public/{$filePath}");
        file_put_contents($fullPath, $imageData);
        
        // Create asset record
        $asset = Asset::create([
            'prompt_id' => $prompt->id,
            'category_id' => $prompt->category_id,
            'file_path' => $filePath,
            'file_type' => 'image',
            'file_name' => $fileName,
            'status' => 'draft',
        ]);

        // Generate metadata for Adobe Stock
        $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
        $asset->update($metadata);

        return $asset;
    }

    /**
     * Batch generate images
     */
    public function batchGenerate(int $categoryId, int $count = 5): array
    {
        $category = \App\Models\Category::findOrFail($categoryId);
        $generated = [];
        
        // Get or generate prompts
        $prompts = Prompt::where('category_id', $categoryId)
            ->where('status', 'draft')
            ->limit($count)
            ->get();
        
        // If not enough prompts, generate new ones
        if ($prompts->count() < $count) {
            $promptService = app(PromptGeneratorService::class);
            for ($i = 0; $i < $count - $prompts->count(); $i++) {
                $result = $promptService->generateAuto($category->slug, 'en', 'image');
                $result['category_id'] = $categoryId;
                $prompts->push($promptService->createPrompt($result));
            }
        }
        
        foreach ($prompts as $prompt) {
            try {
                $asset = $this->generateImage($prompt->id, $category->slug);
                $prompt->update(['status' => 'generated']);
                $generated[] = $asset;
            } catch (\Exception $e) {
                \Log::error("Failed to generate image: " . $e->getMessage());
            }
        }
        
        return $generated;
    }

    /**
     * Upload existing image
     */
    public function uploadImage(UploadedFile $file, int $categoryId, ?int $promptId = null): Asset
    {
        $category = \App\Models\Category::findOrFail($categoryId);
        
        $fileName = Str::slug($category->name) . '_' . time() . '.' . $file->extension();
        $filePath = $file->storeAs('assets', $fileName, 'public');
        
        $asset = Asset::create([
            'prompt_id' => $promptId,
            'category_id' => $categoryId,
            'file_path' => $filePath,
            'file_type' => 'image',
            'file_name' => $fileName,
            'status' => 'draft',
        ]);

        // Generate metadata
        $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
        $asset->update($metadata);

        return $asset;
    }

    /**
     * Create mock image for testing
     */
    protected function createMockImage(string $prompt): string
    {
        // Create a simple placeholder image with gradient
        $img = Image::canvas(800, 600, '#1a1a2e');
        
        // Add some visual interest
        $img->rectangle(0, 0, 800, 600, function ($draw) {
            $draw->background('linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
        });
        
        // Add text (shortened prompt)
        $shortPrompt = substr($prompt, 0, 40);
        $img->text($shortPrompt, 400, 300, function ($font) {
            $font->size(16);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });
        
        return $img->encode('jpeg', 90)->getEncoded();
    }

    /**
     * Generate SEO-friendly filename
     */
    protected function generateSeoFileName(string $category, int $promptId, string $extension): string
    {
        $timestamp = date('YmdHis');
        return "{$category}_{$promptId}_{$timestamp}.{$extension}";
    }
}