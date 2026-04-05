<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Prompt;
use Illuminate\Support\Str;

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
     * Create placeholder video file (GIF)
     */
    protected function createMockVideo(string $description): string
    {
        // Create a simple GIF placeholder using GD
        $img = imagecreatetruecolor(640, 360);
        
        $colors = [
            imagecolorallocate($img, 102, 126, 234),
            imagecolorallocate($img, 118, 75, 162),
            imagecolorallocate($img, 240, 147, 251),
            imagecolorallocate($img, 245, 87, 108),
            imagecolorallocate($img, 79, 172, 254),
        ];
        
        $textColor = imagecolorallocate($img, 255, 255, 255);
        
        // Create frame
        $colorIndex = 0;
        imagefilledrectangle($img, 0, 0, 640, 360, $colors[$colorIndex]);
        imagestring($img, 5, 200, 170, "Video: " . substr($description, 0, 25), $textColor);
        
        ob_start();
        imagegif($img);
        $data = ob_get_clean();
        
        imagedestroy($img);
        
        return $data;
    }

    /**
     * Generate SEO-friendly filename
     */
    protected function generateSeoFileName(string $category, int $timestamp, string $extension): string
    {
        return "{$category}_video_{$timestamp}.{$extension}";
    }
}