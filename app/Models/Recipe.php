<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $primaryKey = 'recipe_id';

    protected $guarded = ['recipe_id'];
    protected $appends = ['total_ingredient', 'public_image', 'is_liked', 'likes_count'];

    public function getPublicImageAttribute()
    {
        return $this->image 
            ? asset('assets/food-images') . '/' . $this->image . '.jpg' 
            : asset('assets/sample-images/default-image.png');
    }

    public function dietaryPreferences()
    {
        return $this->belongsToMany(DietaryPreference::class, 'recipe_dietary_preferences', 'recipe_id', 'dietary_preference_id');
    }

    public function allergies()
    {
        return $this->belongsToMany(Allergy::class, 'recipe_allergies', 'recipe_id', 'allergy_id');
    }

    public function getTotalIngredientAttribute()
    {
        return $this->hasMany(RecipeIngredient::class, 'recipe_id', 'recipe_id')->count();
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients', 'recipe_id', 'ingredient_id');
    }

    public function likes()
    {
        return $this->hasMany(LikeRecipe::class, 'recipe_id', 'recipe_id');
    }

    public function getIsLikedAttribute()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->likes()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}