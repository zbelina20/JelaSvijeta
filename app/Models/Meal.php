<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ingredient;

class Meal extends Model
{

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
