<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'description'];

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
