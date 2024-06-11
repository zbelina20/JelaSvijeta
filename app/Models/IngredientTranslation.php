<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title'];
    public $translatedAttributes = ['title'];
}
