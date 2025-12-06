<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $primaryKey = 'recipe_id';

    protected $guarded = ['recipe_id'];
}
