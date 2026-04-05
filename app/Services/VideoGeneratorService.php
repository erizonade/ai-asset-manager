<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Prompt;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class VideoGeneratorService
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
     * Generate video from prompt (mock - creates placeholder)
     */
    public function generateFromPrompt(int $promptId): Asset
    {
        $prompt = Prompt::findOrFail($promptId);
        
        // Create placeholder video file (mock)
        $fileName = $this->generateSeoFileName($prompt->category->slug ?? 'video', $prompt->id, 'mp4');
        $filePath = "assets/{$fileName}";
        
        // Create placeholder video data
        $videoData = $this->createMockVideo($prompt->prompt);
        
        // Save to storage
        $fullPath = storage_path("app/public/{$filePath}");
        file_put_contents($fullPath, $videoData);
        
        // Create asset record
        $asset = Asset::create([
            'prompt_id' => $prompt->id,
            'category_id' => $prompt->category_id,
            'file_path' => $filePath,
            'file_type' => 'video',
            'file_name' => $fileName,
            'status' => 'draft',
        ]);

        // Generate metadata
        $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
        $asset->update($metadata);

        return $asset;
    }

    /**
     * Generate video from image sequence
     */
    public function generateFromImages(array $imagePaths, string $categorySlug): Asset
    {
        $category = \App\Models\Category::where('slug', $categorySlug)->first();
        
        $fileName = $this->generateSeoFileName($categorySlug, time(), 'mp4');
        $filePath = "assets/{$fileName}";
        
        // In production, use FFmpeg to combine images into video
        // For now, create placeholder
        $videoData = $this->createMockVideo('Generated from ' . count($imagePaths) . ' images');
        
        $fullPath = storage_path("app/public/{$filePath}");
        file_put_contents($fullPath, $videoData);
        
        $asset = Asset::create([
            'category_id' => $category?->id,
            'file_path' => $filePath,
            'file_type' => 'video',
            'file_name' => $fileName,
            'status' => 'draft',
        ]);

        // Generate metadata
        $metadata = app(AdobeStockOptimizerService::class)->generateMetadata($asset);
        $asset->update($metadata);

        return $asset;
    }

    /**
     * Create placeholder video file
     */
    protected function createMockVideo(string $description): string
    {
        // Create a simple animated GIF as placeholder
        // In production, use actual FFmpeg or video API
        
        $frames = [];
        $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'];
        
        for ($i = 0; $i < 10; $i++) {
            $img = Image::canvas(640, 360, $colors[$i % count($colors)]);
            $img->text("Video: " . substr($description, 0, 30), 320, 180, function ($font) {
                $font->size(14);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('middle');
            });
            $frames[] = $img->encode('gif');
        }

        // For now, just return a simple placeholder
        // In production: use FFmpeg to create actual video
        return implode('', $frames);
    }

    /**
     * Generate SEO-friendly filename
     */
    protected function generateSeoFileName(string $category, int $timestamp, string $extension): string
    {
        return "{$category}_video_{$timestamp}.{$extension}";
    }
}