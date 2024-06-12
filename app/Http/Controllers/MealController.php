<?php

namespace App\Http\Controllers;

use App\Models\MealTranslation;
use App\Models\TagTranslation;
use App\Models\MealIngredient;
use App\Models\MealTag;
use App\Models\Meal;
use App\Models\IngredientTranslation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MealController extends Controller
{
    public function index(Request $request)
    {
        $rules = [
            'lang' => 'required|string|in:en,hr,de,es,fr',
        ];

        $messages = [
            'lang.required' => 'You need to specify the language.',
            'lang.in' => 'The selected language is not supported.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('lang')
            ], 400);
        }

        $lang = $request->input('lang');
        $with = $request->input('with');
        $perPage = $request->filled('per_page') ? $request->input('per_page') : 10;
        $page = $request->filled('page') ? $request->input('page') : 1;
        $diffTime = $request->filled('diff_time') ? (int) $request->input('diff_time') : 0;

        $query = MealTranslation::where('locale', $lang);

        if ($diffTime > 0) {
            $dateTime = Carbon::createFromTimestamp($diffTime)->setTimezone('CEST')->toDateTimeString();
            //dd($dateTime);
        
            $query->whereHas('meal', function ($mealQuery) use ($dateTime) {
                $mealQuery->where(function ($mealQuery) use ($dateTime) {
                    $mealQuery->where('created_at', '>=', $dateTime)
                        ->orWhere('updated_at', '>=', $dateTime);
                });
            });
        }

        $meals = $query->paginate($perPage, ['*'], 'page', $page);

        if ($meals->isEmpty()) {
            return response()->json([
                'message' => 'No meals found for the specified language or in the time period you provided.'
            ], 404);
        }

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

            $meals->transform(function ($meal) use ($mealsWithCategories, $lang) {
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

        $data = $meals->map(function ($meal) {
            return [
                'id' => $meal->id,
                'title' => $meal->title,
                'description' => $meal->description,
                'status' => $meal->status,
                'category' => $meal->category,
                'ingredients' => $meal->ingredients ?? null,
                'tags' => $meal->tags ?? null,
            ];
        });

        $meta = [
            'currentPage' => $meals->currentPage(),
            'totalItems' => $meals->total(),
            'itemsPerPage' => $meals->perPage(),
            'totalPages' => $meals->lastPage(),
        ];

        $baseUrl = $request->url() . '?' . http_build_query($request->except('page'));
        $currentPage = $meals->currentPage();
        $prevPage = $currentPage > 1 ? $baseUrl . '&page=' . ($currentPage - 1) : null;
        $nextPage = $meals->hasMorePages() ? $baseUrl . '&page=' . ($currentPage + 1) : null;
        $selfPage = $baseUrl . '&page=' . $currentPage;

        $prevPage = $prevPage ? str_replace('%2C', ',', $prevPage) : null;
        $nextPage = $nextPage ? str_replace('%2C', ',', $nextPage) : null;
        $selfPage = str_replace('%2C', ',', $selfPage);

        $links = [
            'prev' => $prevPage,
            'next' => $nextPage,
            'self' => $selfPage,
        ];

        $response = [
            'meta' => $meta,
            'data' => $data,
            'links' => $links,
        ];

        return response()->json($response);
    }
}
