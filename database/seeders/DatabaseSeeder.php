<?php

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            User::factory(10)->create(),
            CategoriesTableSeeder::class,
            IngredientsTableSeeder::class,
            TagsTableSeeder::class,
            MealsTableSeeder::class,
            LanguagesTableSeeder::class,
            CategoryTranslationsTableSeeder::class,
            IngredientTranslationsTableSeeder::class,
            TagTranslationsTableSeeder::class,
            MealTranslationsTableSeeder::class,
        ]);
    }
}
