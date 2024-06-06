<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Broj zapisa po stranici, zadani 10 ako nije navedeno
        $page = $request->input('page', 1); // Broj stranice, zadani 1 ako nije navedeno

        // Dohvati zapise iz baze podataka koristeći Eloquent
        $meals = Meal::paginate($perPage, ['*'], 'page', $page);

        // Izračunaj metapodatke
        $meta = [
            'currentPage' => $meals->currentPage(),
            'totalItems' => $meals->total(),
            'itemsPerPage' => $meals->perPage(),
            'totalPages' => $meals->lastPage(),
        ];

        // Formatiraj odgovor
        $response = [
            'meta' => $meta,
            'data' => $meals->items(),
        ];

        return response()->json($response);
    }
}

//U ovom primjeru, koristimo paginate() metodu za dohvaćanje zapisa iz baze podataka s paginacijom. 
//Koristimo parametre $perPage i $page kako bismo omogućili korisniku da definira broj zapisa po stranici i broj stranice u URL-u.

//Zatim izračunavamo metapodatke o trenutnoj stranici, ukupnom broju zapisa, broju zapisa po stranici i ukupnom broju stranica.

//Na kraju, vraćamo odgovor koji uključuje metapodatke i podatke o jelima.



