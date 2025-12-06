<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikeRecipe extends Model
{
    protected $primaryKey = 'like_recipe_id';
    protected $guarded = ['like_recipe_id'];
}
