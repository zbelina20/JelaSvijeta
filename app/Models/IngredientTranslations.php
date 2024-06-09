<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class IngredientTranslation extends Model implements TranslatableContract
{
    use Translatable;

    public $timestamps = false;
    protected $fillable = ['title'];
    public $translatedAttributes = ['title'];
}
