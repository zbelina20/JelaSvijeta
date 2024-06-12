<?php 

namespace App\Services;

use App\Models\MealTranslation;
use App\Models\TagTranslation;
use App\Models\MealIngredient;
use App\Models\MealTag;
use App\Models\Meal;
use App\Models\IngredientTranslation;
use Carbon\Carbon;

class MealService
{
    public function getFilteredMeals(array $filters)
    {
        $lang = $filters['lang'];
        $with = $filters['with'] ?? null;
        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;
        $diffTime = isset($filters['diff_time']) ? (int) $filters['diff_time'] : 0;

        $query = MealTranslation::where('locale', $lang);

        if ($diffTime > 0) {
            $dateTime = Carbon::createFromTimestamp($diffTime)->setTimezone('CEST')->toDateTimeString();
            $query->whereHas('meal', function ($mealQuery) use ($dateTime) {
                $mealQuery->where(function ($mealQuery) use ($dateTime) {
                    $mealQuery->where('created_at', '>=', $dateTime)
                        ->orWhere('updated_at', '>=', $dateTime);
                });
            });
        }

        $meals = $query->paginate($perPage, ['*'], 'page', $page);

        if ($with && in_array('ingredients', explode(',', $with))) {
            $mealIds = $meals->pluck('meal_id')->toArray();
            $mealIngredients = MealIngredient::whereIn('meal_id', $mealIds)->get();

            $meals->each(function ($meal) use ($lang, $mealIngredients) {
                $ingredients = $mealIngredients->where('meal_id', $meal->meal_id)->pluck('ingredient_id');
                $ingredientTranslations = IngredientTranslation::whereIn('ingredient_id', $ingredients)
                    ->where('locale', $lang)
                    ->get();

                $formattedIngredients = $ingredientTranslations->map(function ($ingredientTranslation) {
                    return [
                        'id' => $ingredientTranslation->ingredient_id,
                        'title' => $ingredientTranslation->title,
                        'slug' => $ingredientTranslation->slug,
                    ];
                });

                $meal->ingredients = $formattedIngredients->toArray();
            });
        }

        if ($with && in_array('category', explode(',', $with))) {
            $mealIds = $meals->pluck('meal_id')->toArray();

            $mealsWithCategories = Meal::whereIn('id', $mealIds)
                ->with(['category.translations' => function ($query) use ($lang) {
                    $query->where('locale', $lang);
                }])
                ->get();

            $meals->transform(function ($meal) use ($mealsWithCategories) {
                $mealWithCategory = $mealsWithCategories->firstWhere('id', $meal->meal_id);
                $categoryTranslation = null;

                if ($mealWithCategory->category) {
                    $categoryTranslation = $mealWithCategory->category->translations->first();
                }

                $meal->category = $mealWithCategory->category
                    ? [
                        'id' => $mealWithCategory->category->id,
                        'title' => $categoryTranslation ? $categoryTranslation->title : null,
                        'slug' => $mealWithCategory->category->slug,
                    ]
                    : null;

                $meal->status = $mealWithCategory->status;

                return $meal;
            });
        }

        if ($with && in_array('tags', explode(',', $with))) {
            $mealIds = $meals->pluck('meal_id')->toArray();
            $mealTags = MealTag::whereIn('meal_id', $mealIds)->get();

            $meals->each(function ($meal) use ($lang, $mealTags) {
                $tagIds = $mealTags->where('meal_id', $meal->meal_id)->pluck('tag_id')->toArray();
                $tagTranslations = TagTranslation::whereIn('tag_id', $tagIds)
                    ->where('locale', $lang)
                    ->get();

                $formattedTags = $tagTranslations->map(function ($tagTranslation) {
                    return [
                        'id' => $tagTranslation->tag_id,
                        'title' => $tagTranslation->title,
                        'slug' => $tagTranslation->slug,
                    ];
                });

                $meal->tags = $formattedTags->toArray();
            });
        }

        return $meals;
    }
}
