<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MealTranslationsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 20) as $index) {
            DB::table('meal_translations')->insert([
                'meal_id' => $index,
                'locale' => 'hr',
                'title' => 'HR - ' . $faker->sentence(3),
                'description' => 'HR - ' . $faker->paragraph,
            ]);
            DB::table('meal_translations')->insert([
                'meal_id' => $index,
                'locale' => 'en',
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph,
            ]);
            DB::table('meal_translations')->insert([
                'meal_id' => $index,
                'locale' => 'de',
                'title' => 'DE - ' . $faker->sentence(3),
                'description' => 'DE - ' . $faker->paragraph,
            ]);
            DB::table('meal_translations')->insert([
                'meal_id' => $index,
                'locale' => 'es',
                'title' => 'ES - ' . $faker->sentence(3),
                'description' => 'ES - ' . $faker->paragraph,
            ]);
            DB::table('meal_translations')->insert([
                'meal_id' => $index,
                'locale' => 'fr',
                'title' => 'FR - ' . $faker->sentence(3),
                'description' => 'FR - ' . $faker->paragraph,
            ]);
        }
    }
}
