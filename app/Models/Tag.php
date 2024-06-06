<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public function meals()
    {
        return $this->belongsToMany(Meal::class);
    }
}
