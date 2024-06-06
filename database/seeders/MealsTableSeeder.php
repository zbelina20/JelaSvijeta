<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ingredient;

class MealsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $tagIds = Tag::pluck('id')->toArray();
        $ingredientIds = Ingredient::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();
        
        for ($i = 0; $i < 20; $i++) {
            $mealId = DB::table('meals')->insertGetId([
                'title' => $faker->sentence,
                'description' => $faker->paragraph,
                'status' => $faker->randomElement(['created', 'modified', 'deleted']),
                'category_id' => $faker->optional()->randomElement($categoryIds), // 50% chance to be null
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Attach tags
            $mealTags = $faker->randomElements($tagIds, $faker->numberBetween(1, 3));
            foreach ($mealTags as $tagId) {
                DB::table('meal_tag')->insert([
                    'meal_id' => $mealId,
                    'tag_id' => $tagId,
                ]);
            }

            // Attach ingredients
            $mealIngredients = $faker->randomElements($ingredientIds, $faker->numberBetween(1, 3));
            foreach ($mealIngredients as $ingredientId) {
                DB::table('meal_ingredient')->insert([
                    'meal_id' => $mealId,
                    'ingredient_id' => $ingredientId,
                ]);
            }
        }
    }
}
