<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MealService;
use Illuminate\Support\Facades\Log;

class MealController extends Controller
{
    protected $mealService;

    public function __construct(MealService $mealService)
    {
        $this->mealService = $mealService;
    }

    public function index(Request $request)
    {

        $rules = [
            'lang' => 'required|string|in:en,hr,de,es,fr',
        ];

        $messages = [
            'lang.required' => 'You need to specify the language of the meals.',
            'lang.in' => 'The selected language is not supported.',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('lang')
            ], 400);
        }

        $data = $request->only(['lang', 'with', 'per_page', 'page', 'diff_time', 'category', 'tags']);

        try {
            $meals = $this->mealService->getFilteredMeals($data);
            $filteredMeals = $meals->getCollection()->transform(function ($meal) {
                if (!is_null($meal->status)) {
                    return $meal;
                }
            })->filter();
        } catch (\Exception $e) {
            \Log::error('Exception occurred: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error retrieving meals.'], 500);
        }

        if ($meals->isEmpty()) {
            return response()->json([
                'message' => 'No meals found for the specified language or in the time period you provided.'
            ], 404);
        }

        $mealData = $filteredMeals->reject(function ($meal) {
            return is_null($meal);
        })->map(function ($meal) {
            $mealArray = [
                'id' => $meal->id,
                'title' => $meal->title,
                'description' => $meal->description,
                'status' => $meal->status,
            ];

            if (!is_null($meal->category)) {
                $mealArray['category'] = $meal->category;
            }

            if (!is_null($meal->ingredients)) {
                $mealArray['ingredients'] = $meal->ingredients;
            }

            if (!is_null($meal->tags)) {
                $mealArray['tags'] = $meal->tags;
            }

            return $mealArray;
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
            'data' => $mealData,
            'links' => $links,
        ];

        return response()->json($response);
    }
}
