<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Bisnis',
                'slug' => 'bisnis',
                'description' => 'Business and corporate themed content for stock media',
                'keywords' => ['business', 'office', 'corporate', 'professional', 'meeting', 'workspace', 'entrepreneur', 'startup', 'success', 'management'],
                'is_active' => true,
            ],
            [
                'name' => 'Teknologi',
                'slug' => 'teknologi',
                'description' => 'Technology and digital innovation content',
                'keywords' => ['technology', 'tech', 'digital', 'innovation', 'future', 'modern', 'futuristic', 'cyber', 'electronic', 'computer', 'smartphone', 'ai'],
                'is_active' => true,
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Modern lifestyle and living concepts',
                'keywords' => ['lifestyle', 'modern', 'living', 'home', 'interior', 'design', 'cozy', 'comfort', 'relaxation', 'leisure', 'travel', 'fashion'],
                'is_active' => true,
            ],
            [
                'name' => 'Alam',
                'slug' => 'alam',
                'description' => 'Nature and outdoor landscape content',
                'keywords' => ['nature', 'landscape', 'scenic', 'outdoor', 'beautiful', 'mountain', 'forest', 'ocean', 'beach', 'sunset', 'flower', 'wildlife'],
                'is_active' => true,
            ],
            [
                'name' => 'Pendidikan',
                'slug' => 'pendidikan',
                'description' => 'Education and learning themed content',
                'keywords' => ['education', 'learning', 'study', 'school', 'university', 'student', 'teacher', 'classroom', 'knowledge', 'science', 'research', 'book'],
                'is_active' => true,
            ],
            [
                'name' => 'Kesehatan',
                'slug' => 'kesehatan',
                'description' => 'Healthcare and wellness content',
                'keywords' => ['health', 'healthcare', 'medical', 'hospital', 'doctor', 'patient', 'wellness', 'healthy', 'fitness', 'nutrition', 'medicine', 'treatment'],
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}