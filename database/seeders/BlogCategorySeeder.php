<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Travel Tips', 'description' => 'Advice for smoother, more comfortable journeys.'],
            ['name' => 'Company News', 'description' => 'Announcements and updates from our team.'],
            ['name' => 'Luxury Lifestyle', 'description' => 'Stories from the world of premium travel and hospitality.'],
            ['name' => 'Industry Insights', 'description' => 'Trends shaping the chauffeur and transportation industry.'],
        ];

        foreach ($categories as $index => $category) {
            BlogCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['sort_order' => $index + 1, 'is_active' => true]
            );
        }
    }
}
