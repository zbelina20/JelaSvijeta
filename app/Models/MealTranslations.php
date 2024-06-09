<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class MealTranslation extends Model implements TranslatableContract
{
    use Translatable;

    public $timestamps = false;
    protected $fillable = ['title', 'description'];
    public $translatedAttributes = ['title', 'description'];
}
