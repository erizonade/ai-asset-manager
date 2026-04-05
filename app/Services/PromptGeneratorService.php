<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Prompt;
use Illuminate\Support\Str;

class PromptGeneratorService
{
    /**
     * Templates per category for AI generation
     */
    protected array $templates = [
        'bisnis' => [
            'image' => [
                'Modern office workspace with {subject}, clean minimalist style, professional lighting, 4k quality',
                'Business meeting in modern conference room, corporate setting, natural daylight',
                'Entrepreneur working at desk, startup environment, creative atmosphere',
                'Business professionals shaking hands, partnership concept, professional background',
                'Office interior design, modern furniture, sleek workspace aesthetics',
            ],
            'video' => [
                'Business presentation animation, smooth transitions, corporate style',
                'Time-lapse of office activities, professional workflow, modern setting',
            ],
        ],
        'teknologi' => [
            'image' => [
                'Futuristic technology concept, neon lighting, cyberpunk aesthetic, digital innovation',
                'AI and machine learning visualization, neural network graphics, tech background',
                'Modern smartphone and tablet, cutting-edge device mockup, clean studio lighting',
                'Server room data center, technology infrastructure, blue LED lighting',
                'Coding and programming concept, developer workspace, multiple monitors',
            ],
            'video' => [
                'Technology animation loop, digital transformation concept, futuristic elements',
                'Data flow visualization, network connections, tech-inspired motion graphics',
            ],
        ],
        'lifestyle' => [
            'image' => [
                'Modern lifestyle concept, healthy living, wellness and balance',
                'Coffee shop atmosphere, relaxation concept, cozy modern setting',
                'Fitness and exercise concept, gym workout, healthy lifestyle',
                'Travel and adventure, exploration and discovery, modern wanderlust',
                'Home interior design, comfortable living space, modern aesthetics',
            ],
            'video' => [
                'Lifestyle montage, smooth transitions, modern living concept',
                'Relaxation and wellness animation, peaceful movement, calming visuals',
            ],
        ],
        'alam' => [
            'image' => [
                'Beautiful landscape nature, mountain scenery, outdoor adventure',
                'Ocean and beach sunset, tropical paradise, coastal scenery',
                'Forest and trees, green nature, peaceful wilderness',
                'Flowers and garden, botanical beauty, natural colors',
                'Sky and clouds, atmospheric landscape, natural beauty',
            ],
            'video' => [
                'Nature time-lapse, clouds moving, peaceful natural scenery',
                'Ocean waves animation, calming water movement, nature loop',
            ],
        ],
        'pendidikan' => [
            'image' => [
                'Online learning concept, e-education, digital classroom',
                'Student studying, knowledge acquisition, academic setting',
                'Books and education materials, learning resources, study environment',
                'Science and research, laboratory, academic discovery',
                'Teacher and students, classroom setting, educational interaction',
            ],
            'video' => [
                'Educational animation, learning process, knowledge visualization',
                'Book pages turning, study concept, educational motion graphics',
            ],
        ],
        'kesehatan' => [
            'image' => [
                'Healthcare and medical concept, hospital environment, professional care',
                'Doctor and patient, medical consultation, health service',
                'Wellness and health, lifestyle improvement, preventive care',
                'Medical equipment and technology, healthcare innovation',
                'Healthy food and nutrition, balanced diet concept',
            ],
            'video' => [
                'Healthcare animation, medical concept, health awareness',
                'Heartbeat medical animation, health monitoring visualization',
            ],
        ],
    ];

    /**
     * Generate auto prompt based on category
     */
    public function generateAuto(string $categorySlug, string $language = 'en', string $type = 'image'): array
    {
        $category = Category::where('slug', $categorySlug)->first();
        
        if (!$category) {
            // Try to find by name
            $category = Category::where('name', $categorySlug)->first();
        }

        $templates = $this->templates[$categorySlug] ?? $this->templates['bisnis'];
        $typeTemplates = $templates[$type] ?? $templates['image'];
        
        $template = $typeTemplates[array_rand($typeTemplates)];
        
        // Add variation based on keywords
        $subjects = $category?->keywords ?? ['business', 'modern', 'professional'];
        $subject = $subjects[array_rand($subjects)];
        
        $prompt = str_replace('{subject}', $subject, $template);
        
        return [
            'category_id' => $category?->id,
            'category_slug' => $categorySlug,
            'prompt' => $prompt,
            'language' => $language,
            'type' => $type,
        ];
    }

    /**
     * Create and save prompt to database
     */
    public function createPrompt(array $data): Prompt
    {
        return Prompt::create([
            'category_id' => $data['category_id'],
            'prompt' => $data['prompt'],
            'language' => $data['language'] ?? 'en',
            'type' => $data['type'] ?? 'image',
            'status' => 'draft',
        ]);
    }

    /**
     * Generate daily prompts for all categories
     */
    public function generateDaily(): array
    {
        $categories = Category::where('is_active', true)->get();
        $generated = [];

        foreach ($categories as $category) {
            // Generate 2 prompts per category
            for ($i = 0; $i < 2; $i++) {
                $result = $this->generateAuto($category->slug, 'en', 'image');
                $result['category_id'] = $category->id;
                
                $prompt = $this->createPrompt($result);
                $generated[] = $prompt;
            }
        }

        return $generated;
    }

    /**
     * Get available categories for prompt generation
     */
    public function getCategories(): array
    {
        return array_keys($this->templates);
    }
}