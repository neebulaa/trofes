<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeAllergy extends Model
{
    protected $primaryKey = 'recipe_allergy_id';

    protected $guarded = ['recipe_allergy_id'];
}
