<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class Tag extends Model
{
    use Translatable;
    public $translatedAttributes = ['title'];
    public function tags()
    {
        return $this->belongsToMany(Meal::class);
    }
}
