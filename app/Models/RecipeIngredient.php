<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    
    protected $primaryKey = 'recipe_ingredient_id';
    protected $guarded = ['recipe_ingredient_id'];
}
