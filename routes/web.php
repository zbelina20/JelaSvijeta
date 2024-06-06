<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/meals', [MealController::class, 'index']);
