<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ingredient;
use Astrotomic\Translatable\Translatable;

class Meal extends Model
{
    use Translatable;
    public $translatedAttributes = ['title', 'description'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'category_id',
    ];

    /**
     * Get the category of the meal.
     */
    public function category()
    {
        return $this->hasOne(Category::class);
    }

    /**
     * Get the tags of the meal.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the ingredients of the meal.
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
