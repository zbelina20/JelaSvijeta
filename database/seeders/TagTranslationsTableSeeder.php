<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TagTranslationsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 20) as $index) {
            DB::table('tag_translations')->insert([
                'tag_id' => $index,
                'locale' => 'hr',
                'title' => 'HR - ' . $faker->word,
            ]);
            DB::table('tag_translations')->insert([
                'tag_id' => $index,
                'locale' => 'en',
                'title' => $faker->word,
            ]);
            DB::table('tag_translations')->insert([
                'tag_id' => $index,
                'locale' => 'de',
                'title' => 'DE - ' . $faker->word,
            ]);
            DB::table('tag_translations')->insert([
                'tag_id' => $index,
                'locale' => 'es',
                'title' => 'ES - ' . $faker->word,
            ]);
            DB::table('tag_translations')->insert([
                'tag_id' => $index,
                'locale' => 'fr',
                'title' => 'FR - ' . $faker->word,
            ]);
        }
    }
}
