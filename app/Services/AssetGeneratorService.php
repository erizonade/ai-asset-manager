<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Prompt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssetGeneratorService
{
    protected string $storagePath;
    
    // AI Provider: 'huggingface', 'dalle', or 'none' for placeholder
    protected string $aiProvider;
    protected string $huggingfaceToken;
    protected string $openaiApiKey;

    public function __construct()
    {
        $this->storagePath = storage_path('app/public/assets');
        
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
        
        // Load configuration
        $this->aiProvider = config('services.ai_image_provider', 'none');
        $this->huggingfaceToken = config('services.huggingface_token', '');
        $this->openaiApiKey = config('services.openai_api_key', '');
    }

    /**
     * Generate image - tries AI first, fallback to placeholder
     */
    public function generateImage(int $promptId, string $categorySlug): Asset
    {
        $prompt = Prompt::findOrFail($promptId);
        
        // Try AI generation (non-blocking with quick timeout)
        $imageData = $this->tryGenerateAI($prompt->prompt);
        
        // Fallback to placeholder if AI takes too long or fails
        if (empty($imageData)) {
            $imageData = $this->createPlaceholderImage($prompt->prompt, $categorySlug);
        }
        
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
     * Try to generate AI image with short timeout
     */
    protected function tryGenerateAI(string $prompt): ?string
    {
        // Skip AI for now - use placeholder instantly
        // AI generation can be slow and cause timeouts
        return null;
    }

    /**
     * Upscale an existing image (4x resolution)
     */
    public function upscale(Asset $asset): Asset
    {
        $currentPath = storage_path('app/public/' . $asset->file_path);
        
        if (!file_exists($currentPath)) {
            throw new \Exception("Image file not found");
        }
        
        // Get current image data
        $imageData = file_get_contents($currentPath);
        
        try {
            // Try Pollinations upscale
            $url = 'https://image.pollinations.ai/upscale?upscale=4';
            
            $response = Http::timeout(60)
                ->attach('image', $imageData, 'image.jpg')
                ->post($url);
            
            if ($response->successful() && strlen($response->body()) > 10000) {
                // Save upscaled image
                $newPath = str_replace('.jpg', '_upscaled.jpg', $currentPath);
                file_put_contents($newPath, $response->body());
                
                // Update asset
                $asset->update([
                    'file_path' => 'assets/' . basename($newPath),
                    'file_name' => basename($newPath),
                ]);
                
                return $asset;
            }
        } catch (\Exception $e) {
            Log::warning("Upscale failed: " . $e->getMessage());
        }
        
        // If upscale fails, return original
        return $asset;
    }

    /**
     * Generate and auto-upscale image
     */
    public function generateWithUpscale(int $promptId, string $categorySlug): Asset
    {
        $prompt = Prompt::findOrFail($promptId);
        
        // Try AI generation first
        $imageData = $this->tryGenerateAI($prompt->prompt);
        
        // Fallback to placeholder if AI fails
        if (empty($imageData)) {
            $imageData = $this->createPlaceholderImage($prompt->prompt, $categorySlug);
        }
        
        // Generate SEO-friendly filename
        $fileName = $this->generateSeoFileName($categorySlug, $prompt->id, 'jpg');
        $filePath = "assets/{$fileName}";
        
        // Save initial image
        $fullPath = storage_path("app/public/{$filePath}");
        file_put_contents($fullPath, $imageData);
        
        // Try to upscale
        try {
            $url = 'https://image.pollinations.ai/upscale?upscale=4';
            $response = Http::timeout(45)->attach('image', $imageData, 'image.jpg')->post($url);
            
            if ($response->successful() && strlen($response->body()) > 10000) {
                $upscaledPath = str_replace('.jpg', '_4k.jpg', $fullPath);
                file_put_contents($upscaledPath, $response->body());
                $fullPath = $upscaledPath;
                $fileName = basename($upscaledPath);
                $filePath = 'assets/' . $fileName;
            }
        } catch (\Exception $e) {
            Log::warning("Auto-upscale failed, using original: " . $e->getMessage());
        }
        
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
     * Generate using Pollinations.ai - free, no API key needed
     */
    protected function generateWithPollinations(string $prompt): ?string
    {
        // Use fast endpoint with shorter timeout
        $url = 'https://image.pollinations.ai/prompt/' . urlencode($prompt) . '?width=512&height=512&nologin=true&seed=' . rand(1, 999999);
        
        $response = Http::timeout(45)->get($url);
        
        if ($response->successful() && strlen($response->body()) > 5000) {
            return $response->body();
        }
        
        Log::error("Pollinations error: " . $response->status());
        return null;
    }

    /**
     * Generate using Hugging Face
     */
    protected function generateWithHuggingFace(string $prompt): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->huggingfaceToken,
        ])
        ->timeout(180)
        ->post('https://router.huggingface.co/stabilityai/stable-diffusion-2-1-base', [
            'inputs' => $prompt,
        ]);

        if ($response->successful()) {
            return $response->body();
        }
        
        Log::error("Hugging Face error: " . $response->status() . " - " . $response->body());
        return null;
    }

    /**
     * Generate using DALL-E API
     */
    protected function generateWithDallE(string $prompt): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)
        ->post('https://api.openai.com/v1/images/generations', [
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'b64_json',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data'][0]['b64_json'])) {
                return base64_decode($data['data'][0]['b64_json']);
            }
        }
        
        Log::error("DALL-E error: " . $response->status() . " - " . $response->body());
        return null;
    }

    /**
     * Create placeholder image (fallback)
     */
    protected function createPlaceholderImage(string $prompt, string $category): string
    {
        $img = imagecreatetruecolor(1024, 1024);
        
        // Category-based colors
        $categoryColors = [
            'bisnis' => [41, 53, 72, 52, 72, 94],
            'teknologi' => [15, 23, 42, 99, 102, 126],
            'lifestyle' => [251, 191, 36, 245, 158, 11],
            'alam' => [34, 197, 94, 22, 163, 74],
            'pendidikan' => [59, 130, 246, 37, 99, 235],
            'kesehatan' => [236, 72, 153, 244, 63, 94],
        ];
        
        $colors = $categoryColors[$category] ?? [30, 41, 59, 71, 85, 129];
        
        $bgColor = imagecolorallocate($img, $colors[0], $colors[1], $colors[2]);
        $accentColor = imagecolorallocate($img, $colors[3], $colors[4], $colors[5]);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        
        imagefill($img, 0, 0, $bgColor);
        
        // Add geometric pattern
        for ($i = 0; $i < 10; $i++) {
            $x = rand(0, 800);
            $y = rand(0, 800);
            $size = rand(100, 400);
            imagefilledellipse($img, $x, $y, $size, $size, $accentColor);
        }
        
        // Add text
        $shortPrompt = substr($prompt, 0, 60);
        $lines = str_split($shortPrompt, 30);
        $y = 400;
        foreach ($lines as $line) {
            imagestring($img, 5, 200, $y, $line, $textColor);
            $y += 20;
        }
        
        imagestring($img, 7, 400, 950, "Category: " . strtoupper($category), $textColor);
        
        ob_start();
        imagejpeg($img, null, 90);
        $data = ob_get_clean();
        
        imagedestroy($img);
        
        return $data;
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
     * Generate SEO-friendly filename
     */
    protected function generateSeoFileName(string $category, int $promptId, string $extension): string
    {
        $timestamp = date('YmdHis');
        return "{$category}_{$promptId}_{$timestamp}.{$extension}";
    }
}