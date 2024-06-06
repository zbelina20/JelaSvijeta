<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        // Dohvati sva jela
        $query = Meal::query();

        // Filtriranje po kategoriji
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filtriranje po tagovima
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('id', $tags);
            });
        }

        // Ako su definirani dodatni podaci za uključivanje
        if ($request->has('with')) {
            $with = explode(',', $request->with);
            $query->with($with);
        }

        // Straničenje
        $perPage = $request->input('per_page', 10);
        $meals = $query->paginate($perPage);

        return response()->json($meals);
    }
}

