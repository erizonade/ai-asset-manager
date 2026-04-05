<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Support\Str;

class AdobeStockOptimizerService
{
    /**
     * Stock keywords database per category
     */
    protected array $stockKeywords = [
        'bisnis' => [
            'business', 'office', 'corporate', 'professional', 'meeting', 'workspace',
            'entrepreneur', 'startup', 'success', 'management', 'strategy', 'teamwork',
            'leadership', 'conference', 'desk', 'computer', 'laptop', 'technology',
            'modern', 'minimalist', 'clean', 'design', 'interior', 'architecture',
            'finance', 'investment', 'marketing', 'sales', 'negotiation', 'handshake',
            'partnership', 'collaboration', 'communication', 'presentation', 'charts',
            'graphs', 'data', 'analytics', 'digital', 'innovation', 'growth', 'profit',
            'deadline', 'planning', 'organization', 'productivity', 'efficient', 'briefing',
        ],
        'teknologi' => [
            'technology', 'tech', 'digital', 'innovation', 'future', 'modern', 'futuristic',
            'cyber', 'electronic', 'computer', 'smartphone', 'tablet', 'device',
            'screen', 'display', 'interface', 'app', 'software', 'coding', 'programming',
            'developer', 'data', 'network', 'server', 'cloud', 'ai', 'artificial intelligence',
            'machine learning', 'robot', 'automation', 'robotics', 'virtual', 'ar', 'vr',
            'innovation', 'startup', 'sci-fi', 'neon', 'led', 'circuit', 'chip',
            'wireless', 'connection', 'signal', 'iot', 'smart', 'gadget', 'hardware',
        ],
        'lifestyle' => [
            'lifestyle', 'modern', 'living', 'home', 'interior', 'design', 'cozy',
            'comfort', 'relaxation', 'leisure', 'hobby', 'coffee', 'cafe', 'restaurant',
            'food', 'cooking', 'healthy', 'wellness', 'fitness', 'exercise', 'gym',
            'yoga', 'meditation', 'travel', 'adventure', 'exploration', 'vacation',
            'beach', 'mountain', 'nature', 'outdoor', 'activities', 'friends', 'family',
            'relationship', 'love', 'happiness', 'positive', 'vibrant', 'colorful',
            'fashion', 'beauty', 'style', 'trendy', 'urban', 'city', 'lifestyle',
        ],
        'alam' => [
            'nature', 'landscape', 'scenic', 'outdoor', 'beautiful', ' scenery',
            'mountain', 'hill', 'valley', 'forest', 'tree', 'jungle', 'garden',
            'ocean', 'sea', 'beach', 'wave', 'sunset', 'sunrise', 'sky', 'cloud',
            'flower', 'plant', 'wildlife', 'animal', 'bird', 'river', 'lake', 'water',
            'fall', 'autumn', 'spring', 'summer', 'winter', 'season', 'weather',
            'travel', 'adventure', 'hiking', 'camping', 'wilderness', 'peaceful',
            'tranquil', 'serene', 'green', 'blue', 'colorful', 'panoramic', 'vista',
        ],
        'pendidikan' => [
            'education', 'learning', 'study', 'school', 'university', 'college',
            'student', 'teacher', 'professor', 'classroom', 'knowledge', 'science',
            'research', 'book', 'library', 'online', 'e-learning', 'course', 'training',
            'workshop', 'seminar', 'lecture', 'exam', 'degree', 'academic', 'academic',
            'smart', 'intelligent', 'brain', 'idea', 'innovation', 'discovery',
            'experiment', 'laboratory', 'technology', 'digital', 'remote', 'virtual',
            'motivation', 'success', 'career', 'professional', 'development', 'skill',
        ],
        'kesehatan' => [
            'health', 'healthcare', 'medical', 'hospital', 'doctor', 'nurse',
            'patient', 'treatment', 'care', 'wellness', 'healthy', 'fitness', 'exercise',
            'nutrition', 'food', 'diet', 'organic', 'natural', 'medicine', 'pharmacy',
            'therapy', 'rehabilitation', 'prevention', 'checkup', 'diagnostic',
            'equipment', 'technology', 'hospital', 'clinic', 'emergency', 'ambulance',
            'heart', 'pulse', 'vitamins', 'supplement', 'immune', 'wellbeing',
            'mental', 'stress', 'relaxation', 'spa', 'massage', 'holistic', 'balance',
        ],
    ];

    /**
     * Title templates per category
     */
    protected array $titleTemplates = [
        'bisnis' => [
            '{keyword} in modern business environment with professional lighting',
            'Corporate {keyword} concept with clean minimalist design',
            'Professional {keyword} for business and commercial use',
            'Modern office {keyword} - business workspace design',
            '{keyword} - entrepreneurship and success concept',
        ],
        'teknologi' => [
            'Futuristic {keyword} technology concept with neon lighting',
            'Digital {keyword} innovation for modern tech projects',
            'Modern {keyword} with cutting-edge technology design',
            'Tech-inspired {keyword} - future innovation concept',
            'Digital transformation {keyword} - modern technology',
        ],
        'lifestyle' => [
            'Modern {keyword} lifestyle concept for contemporary living',
            'Vibrant {keyword} for lifestyle and wellness projects',
            'Contemporary {keyword} - modern living aesthetic',
            '{keyword} in modern lifestyle setting',
            'Trending {keyword} for lifestyle and design',
        ],
        'alam' => [
            'Beautiful {keyword} landscape nature scenery',
            'Stunning {keyword} nature for outdoor and travel',
            'Peaceful {keyword} natural environment scenery',
            '{keyword} in scenic outdoor nature setting',
            'Breathtaking {keyword} landscape for nature projects',
        ],
        'pendidikan' => [
            'Modern {keyword} education and learning concept',
            'Digital {keyword} for online education projects',
            'Professional {keyword} for academic use',
            '{keyword} in modern educational environment',
            'Contemporary learning {keyword} for study projects',
        ],
        'kesehatan' => [
            'Professional {keyword} healthcare and medical concept',
            'Modern {keyword} for health and wellness projects',
            'Clean {keyword} in healthcare setting',
            '{keyword} - health and wellness lifestyle concept',
            'Contemporary medical {keyword} for healthcare',
        ],
    ];

    /**
     * Description template
     */
    protected string $descriptionTemplate = 'High-quality {type} featuring {keywords}. Perfect for commercial use in advertising, marketing, websites, presentations, and editorial projects. This {type} is professionally created and optimized for stock photography platforms. Keywords: {keyword_list}. Available for immediate download and use.';

    /**
     * Generate full metadata for Adobe Stock
     */
    public function generateMetadata(Asset $asset): array
    {
        $category = $asset->category;
        
        // Generate 49 keywords
        $keywords = $this->generateKeywords($category, 49);
        
        // Generate SEO title
        $title = $this->generateTitle($category);
        
        // Generate description
        $description = $this->generateDescription($category, $asset->file_type, $keywords);
        
        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];
    }

    /**
     * Generate keywords array (49 for Adobe Stock)
     */
    public function generateKeywords(Category $category, int $count = 49): array
    {
        $categoryKeywords = $this->stockKeywords[$category->slug] ?? $this->stockKeywords['bisnis'];
        $baseKeywords = $category->keywords ?? [];
        
        // Combine and deduplicate
        $combined = array_unique(array_merge($categoryKeywords, $baseKeywords));
        
        // Add some universal stock keywords
        $universal = ['stock', 'photo', 'background', 'abstract', 'beautiful', 'high quality', '4k', 'hd'];
        $combined = array_merge($combined, $universal);
        
        // Shuffle and take required count
        shuffle($combined);
        
        return array_slice(array_values($combined), 0, $count);
    }

    /**
     * Generate SEO-optimized title
     */
    public function generateTitle(Category $category): string
    {
        $templates = $this->titleTemplates[$category->slug] ?? $this->titleTemplates['bisnis'];
        $template = $templates[array_rand($templates)];
        
        // Get primary keyword
        $keywords = $this->stockKeywords[$category->slug] ?? $this->stockKeywords['bisnis'];
        $keyword = ucfirst($keywords[array_rand($keywords)]);
        
        return str_replace('{keyword}', $keyword, $template);
    }

    /**
     * Generate description
     */
    public function generateDescription(Category $category, string $fileType, array $keywords): string
    {
        $type = $fileType === 'video' ? 'video footage' : 'photograph';
        
        $keywordList = implode(', ', array_slice($keywords, 0, 10));
        
        return str_replace(
            ['{type}', '{keywords}', '{keyword_list}'],
            [$type, strtolower($category->name), $keywordList],
            $this->descriptionTemplate
        );
    }

    /**
     * Auto rename file to SEO standard
     */
    public function renameFile(Asset $asset): string
    {
        $category = $asset->category?->name ?? 'asset';
        $primaryKeyword = $asset->keywords[0] ?? 'stock';
        
        $seoName = Str::slug("{$primaryKeyword}-{$category}-{$asset->id}");
        $extension = pathinfo($asset->file_name, PATHINFO_EXTENSION);
        
        return "{$seoName}.{$extension}";
    }

    /**
     * Optimize asset for Adobe Stock
     */
    public function optimize(Asset $asset): Asset
    {
        // Generate metadata
        $metadata = $this->generateMetadata($asset);
        
        // Rename file if needed
        $newFileName = $this->renameFile($asset);
        
        // Update asset
        $asset->update(array_merge($metadata, [
            'file_name' => $newFileName,
            'status' => 'ready',
        ]));
        
        return $asset->fresh();
    }
}