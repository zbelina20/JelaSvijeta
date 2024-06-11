<?php

namespace App\Http\Controllers;

use App\Models\MealTranslation;
use App\Models\Meal;
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
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $page = $request->input('page', 1); // Default to 1 if not provided

        // Get the meal translations for the specified language
        $query = MealTranslation::where('locale', $lang);

        // Check for 'with' parameter and add relationships
        if ($with && in_array('category', explode(',', $with))) {
            // Get meal IDs from the translations
            $mealTranslations = $query->paginate($perPage, ['*'], 'page', $page);
            $mealIds = $mealTranslations->pluck('meal_id')->toArray();

            // Get meals with categories
            $mealsWithCategories = Meal::whereIn('id', $mealIds)
                ->with(['category' => function($query) use ($lang) {
                    $query->with(['translations' => function($query) use ($lang) {
                        $query->where('locale', $lang);
                    }]);
                }])
                ->get();

            // Map meals with their categories
            $data = $mealTranslations->map(function ($mealTranslation) use ($mealsWithCategories, $lang) {
                $meal = $mealsWithCategories->firstWhere('id', $mealTranslation->meal_id);
                $categoryTranslation = null;
                if ($meal->category) {
                    $categoryTranslation = $meal->category->translations->first();
                }

                return [
                    'id' => $mealTranslation->id,
                    'title' => $mealTranslation->title,
                    'description' => $mealTranslation->description,
                    'status' => $meal->status,
                    'category' => $meal->category ? [
                        'id' => $meal->category->id,
                        'title' => $categoryTranslation->title ?? null,
                        'slug' => $meal->category->slug,
                    ] : null,
                ];
            });
        } else {
            // Get paginated meal translations
            $mealTranslations = $query->paginate($perPage, ['*'], 'page', $page);
            $data = $mealTranslations->items();
        }

        // Calculate metadata
        $meta = [
            'currentPage' => $mealTranslations->currentPage(),
            'totalItems' => $mealTranslations->total(),
            'itemsPerPage' => $mealTranslations->perPage(),
            'totalPages' => $mealTranslations->lastPage(),
        ];

        // Generate links
        $baseUrl = $request->url() . '?' . http_build_query($request->except('page'));
        $prevPage = $mealTranslations->currentPage() > 1 ? $baseUrl . '&page=' . ($mealTranslations->currentPage() - 1) : null;
        $nextPage = $mealTranslations->hasMorePages() ? $baseUrl . '&page=' . ($mealTranslations->currentPage() + 1) : null;
        $selfPage = $baseUrl . '&page=' . $mealTranslations->currentPage();

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
