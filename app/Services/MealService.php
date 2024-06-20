<?php

namespace App\Services;

use App\Models\MealTranslation;
use App\Models\TagTranslation;
use App\Models\MealIngredient;
use App\Models\MealTag;
use App\Models\Meal;
use App\Models\Tag;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use Carbon\Carbon;

class MealService
{
    public function getFilteredMeals(array $filters)
    {
        $lang = $filters['lang'];
        $with = $filters['with'] ?? null;
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;
        $diffTime = isset($filters['diff_time']) ? (int) $filters['diff_time'] : 0;
        $category = $filters['category'] ?? null;
        $tags = $filters['tags'] ?? null;

        $query = MealTranslation::where('locale', $lang);
        $meals = $query->paginate($perPage, ['*'], 'page', $page);
        $mealIds = $meals->pluck('meal_id')->toArray();
        $statusesQuery = Meal::whereIn('id', $mealIds);

        if ($diffTime > 0) {
            $statuses = $statusesQuery->pluck('status', 'id');
            $dateTime = Carbon::createFromTimestamp($diffTime)->setTimezone('CEST')->toDateTimeString();
            $query->whereHas('meal', function ($mealQuery) use ($dateTime) {
                $mealQuery->where(function ($mealQuery) use ($dateTime) {
                    $mealQuery->where('created_at', '>=', $dateTime)
                        ->orWhere('updated_at', '>=', $dateTime);
                });
            });
        } else {
            $statuses = $statusesQuery->where('status', 'created')->pluck('status', 'id');
        }

        $meals->each(function ($meal) use ($statuses) {
            if (isset($statuses[$meal->meal_id])) {
                $meal->status = $statuses[$meal->meal_id];
            } else {
                unset($meal->status);
            }
        });

        if ($with && in_array('ingredients', explode(',', $with))) {
            $mealIngredients = MealIngredient::whereIn('meal_id', $mealIds)->get();

            $meals->each(function ($meal) use ($lang, $mealIngredients) {
                $ingredients = $mealIngredients->where('meal_id', $meal->meal_id)->pluck('ingredient_id');
                $ingredientTranslations = IngredientTranslation::whereIn('ingredient_id', $ingredients)
                    ->where('locale', $lang)
                    ->get();

                $formattedIngredients = $ingredientTranslations->map(function ($ingredientTranslation) use ($ingredients) {
                    $ingredient = Ingredient::find($ingredientTranslation->ingredient_id);

                    return [
                        'id' => $ingredientTranslation->ingredient_id,
                        'title' => $ingredientTranslation->title,
                        'slug' => $ingredient ? $ingredient->slug : null,
                    ];
                });

                $meal->ingredients = $formattedIngredients->toArray();
            });
        }

        if ($with && in_array('category', explode(',', $with))) {
            $mealIds = $meals->pluck('meal_id')->toArray();

            $mealsWithCategoriesQuery = Meal::whereIn('id', $mealIds);

            if ($category === 'NULL') {
                $mealsWithCategoriesQuery->doesntHave('category');
            } elseif ($category === '!NULL') {
                $mealsWithCategoriesQuery->has('category');
            } elseif ($category && is_numeric($category)) {
                $mealsWithCategoriesQuery->where('category_id', $category);
            }

            $mealsWithCategories = $mealsWithCategoriesQuery
                ->with(['category.translations' => function ($query) use ($lang) {
                    $query->where('locale', $lang);
                }])
                ->get();

            $meals->transform(function ($meal) use ($mealsWithCategories) {
                $mealWithCategory = $mealsWithCategories->firstWhere('id', $meal->meal_id);
                $categoryTranslation = null;

                if ($mealWithCategory && $mealWithCategory->category) {
                    $categoryTranslation = $mealWithCategory->category->translations->first();
                }

                $meal->category = $mealWithCategory && $mealWithCategory->category
                    ? [
                        'id' => $mealWithCategory->category->id,
                        'title' => $categoryTranslation ? $categoryTranslation->title : null,
                        'slug' => $mealWithCategory->category->slug,
                    ]
                    : null;

                return $meal;
            });
        }

        if ($with && in_array('tags', explode(',', $with))) {
            $mealTags = MealTag::whereIn('meal_id', $mealIds)->get();

            $meals->each(function ($meal) use ($lang, $mealTags, $tags) {
                // Iterate over each meal in the $meals collection

                if (!is_null($tags)) {
                    // Split tag IDs from the 'tags' parameter if provided
                    $tagIds = explode(',', $tags);

                    // Check if the current meal has all specified tags
                    $hasAllTags = collect($tagIds)->every(function ($tagId) use ($mealTags, $meal) {
                        return $mealTags->where('meal_id', $meal->meal_id)->pluck('tag_id')->contains($tagId);
                    });

                    if (!$hasAllTags) {
                        // If the meal does not have all specified tags, skip processing
                        return;
                    }
                }

                // Retrieve tag IDs for the current meal
                $tagIds = $mealTags->where('meal_id', $meal->meal_id)->pluck('tag_id')->toArray();

                // Retrieve tag translations for the filtered tag IDs
                $tagTranslations = TagTranslation::whereIn('tag_id', $tagIds)
                    ->where('locale', $lang)
                    ->get();

                // Format tags with their translations
                $formattedTags = $tagTranslations->map(function ($tagTranslation) {
                    // Retrieve the tag details from the Tag model
                    $tag = Tag::find($tagTranslation->tag_id);

                    // Prepare formatted tag data
                    return [
                        'id' => $tagTranslation->tag_id,
                        'title' => $tagTranslation->title,
                        'slug' => $tag ? $tag->slug : null,
                    ];
                });

                // Assign formatted tags to the current meal object
                $meal->tags = $formattedTags->toArray();
            });
        }


        return $meals;
    }
}
