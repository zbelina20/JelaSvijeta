<?php

namespace App\Http\Controllers;

use App\Models\MealTranslation;
use App\Models\MealIngredient;
use App\Models\Meal;
use App\Models\IngredientTranslation;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        // Define validation rules and custom messages
        $rules = [
            'lang' => 'required|string|in:en,hr,de,es,fr', // List of supported languages
        ];

        $messages = [
            'lang.required' => 'You need to specify the language.',
            'lang.in' => 'The selected language is not supported.',
        ];

        // Validate the request parameters
        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('lang')
            ], 400);
        }

        $lang = $request->input('lang');
        $with = $request->input('with');
        $perPage = $request->filled('per_page') ? $request->input('per_page') : 10; // Default to 10 if not provided
        $page = $request->filled('page') ? $request->input('page') : 1; // Default to 1 if not provided

        // Get the meal translations for the specified language
        $meals = MealTranslation::where('locale', $lang)->paginate($perPage, ['*'], 'page', $page);

        // Check if there are any results
        if ($meals->isEmpty()) {
            return response()->json([
                'message' => 'No meals found for the specified language.'
            ], 404);
        }

        // If 'ingredients' are requested, load and transform them
        if ($with && in_array('ingredients', explode(',', $with))) {
            $mealIds = $meals->pluck('meal_id')->toArray();
            $mealIngredients = MealIngredient::whereIn('meal_id', $mealIds)->get();

            $meals->each(function ($meal) use ($lang, $mealIngredients) {
                $ingredients = $mealIngredients->where('meal_id', $meal->meal_id)->pluck('ingredient_id');
                $ingredientTranslations = IngredientTranslation::whereIn('ingredient_id', $ingredients)
                    ->where('locale', $lang)
                    ->get();

                // Format ingredients to match the requested structure
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

        // If 'category' is requested, load and transform it
        if ($with && in_array('category', explode(',', $with))) {
            // Get meal IDs from the translations
            $mealIds = $meals->pluck('meal_id')->toArray();
        
            // Get meals with categories
            $mealsWithCategories = Meal::whereIn('id', $mealIds)
                ->with(['category.translations' => function ($query) use ($lang) {
                    $query->where('locale', $lang);
                }])
                ->get();
        
            // Map meals with their categories
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
        
                // Get status from the Meal model
                $meal->status = $mealWithCategory->status;
        
                return $meal;
            });
        }
        
        // Format the response
        $data = $meals->map(function ($meal) {
            return [
                'id' => $meal->id,
                'title' => $meal->title,
                'description' => $meal->description,
                'status' => $meal->status,
                'category' => $meal->category,
                'ingredients' => $meal->ingredients ?? null,
            ];
        });
        
        // Calculate metadata
        $meta = [
            'currentPage' => $meals->currentPage(),
            'totalItems' => $meals->total(),
            'itemsPerPage' => $meals->perPage(),
            'totalPages' => $meals->lastPage(),
        ];
        
        // Generate links
        $baseUrl = $request->url().'?'.http_build_query($request->except('page'));
        $prevPage = $meals->currentPage() > 1 ? $baseUrl.'&page='.($meals->currentPage() - 1) : null;
        $nextPage = $meals->hasMorePages() ? $baseUrl.'&page='.($meals->currentPage() + 1) : null;
        $selfPage = $baseUrl.'&page='.$meals->currentPage();
        
        $links = [
            'prev' => $prevPage,
            'next' => $nextPage,
            'self' => $selfPage,
        ];
        
        // Format the response
        $response = [
            'meta' => $meta,
            'data' => $data,
            'links' => $links,
        ];
        
        return response()->json($response);
                
    }
}
