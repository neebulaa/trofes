<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $primaryKey = 'recipe_id';

    protected $guarded = ['recipe_id'];
    protected $appends = ['total_ingredient', 'public_image'];

    public function getPublicImageAttribute(){
        return $this->image ? asset('assets/food-images') . '/' . $this->image : null;
    }

    public function dietaryPreferences()
    {
        return $this->belongsToMany(DietaryPreference::class, 'recipe_dietary_preferences', 'recipe_id', 'dietary_preference_id');
    }

    public function allergies()
    {
        return $this->belongsToMany(Allergy::class, 'recipe_allergies', 'recipe_id', 'allergy_id');
    }

    public function getTotalIngredientAttribute(){
        return $this->hasMany(RecipeIngredient::class, 'recipe_id', 'recipe_id')->count();
    }
}
