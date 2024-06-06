<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function meals()
    {
        return $this->belongsTo(Meal::class);
    }
}
