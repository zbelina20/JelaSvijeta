<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CategoryTranslationsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 20) as $index) {
            DB::table('category_translations')->insert([
                'category_id' => $index,
                'locale' => 'hr',
                'title' => 'HR - ' . $faker->sentence(3),
            ]);
            DB::table('category_translations')->insert([
                'category_id' => $index,
                'locale' => 'en',
                'title' => $faker->sentence(3),
            ]);
            DB::table('category_translations')->insert([
                'category_id' => $index,
                'locale' => 'fr',
                'title' => 'FR - ' . $faker->sentence(3),
            ]);
            DB::table('category_translations')->insert([
                'category_id' => $index,
                'locale' => 'de',
                'title' => 'DE - ' . $faker->sentence(3),
            ]);
            DB::table('category_translations')->insert([
                'category_id' => $index,
                'locale' => 'es',
                'title' => 'ES - ' . $faker->sentence(3),
            ]);
        }
    }
}
