<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class Ingredient extends Model
{
    use Translatable;
    public $translatedAttributes = ['title'];
    public function ingredients()
    {
        return $this->belongsToMany(Meal::class);
    }
}
