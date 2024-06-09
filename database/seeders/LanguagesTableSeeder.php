<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            ['name' => 'Croatian', 'code' => 'hr'],
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'Spanish', 'code' => 'es'],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}





