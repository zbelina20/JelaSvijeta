<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
        
        $this->call([
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
