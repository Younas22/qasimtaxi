<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'native_name' => 'English', 'code' => 'en', 'direction' => 'ltr', 'is_default' => true],
            ['name' => 'Arabic', 'native_name' => 'العربية', 'code' => 'ar', 'direction' => 'rtl', 'is_default' => false],
            ['name' => 'Urdu', 'native_name' => 'اردو', 'code' => 'ur', 'direction' => 'rtl', 'is_default' => false],
            ['name' => 'German', 'native_name' => 'Deutsch', 'code' => 'de', 'direction' => 'ltr', 'is_default' => false],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['code' => $language['code']],
                $language + ['is_active' => true]
            );
        }
    }
}
