<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class Category extends Model
{
    use Translatable;
    public $translatedAttributes = ['title'];
    public function categories()
    {
        return $this->belongsTo(Meal::class);
    }
}
