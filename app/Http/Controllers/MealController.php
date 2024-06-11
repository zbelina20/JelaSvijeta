<?php

namespace App\Http\Controllers;

use App\Models\MealTranslation;
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
        $perPage = $request->filled('per_page') ? $request->input('per_page') : 10; // Default to 10 if not provided
        $page = $request->filled('page') ? $request->input('page') : 1; // Default to 1 if not provided

        // Get the meal translations for the specified language
        $meals = MealTranslation::where('locale', $lang)
                                ->paginate($perPage, ['*'], 'page', $page);

        // Check if there are any results
        if ($meals->isEmpty()) {
            return response()->json([
                'message' => 'No meals found for the specified language.'
            ], 404);
        }

        // Calculate metadata
        $meta = [
            'currentPage' => $meals->currentPage(),
            'totalItems' => $meals->total(),
            'itemsPerPage' => $meals->perPage(),
            'totalPages' => $meals->lastPage(),
        ];

        // Generate links
        $baseUrl = $request->url().'?'.$request->getQueryString();
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
            'data' => $meals->items(),
            'links' => $links,
        ];

        return response()->json($response);
    }
}
