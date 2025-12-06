<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeDietaryPreference extends Model
{
    protected $primaryKey = 'recipe_dietary_preference_id';
    protected $guarded = ['recipe_dietary_preference_id'];
}
