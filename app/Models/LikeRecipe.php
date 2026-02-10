<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikeRecipe extends Model
{
    protected $primaryKey = 'like_recipe_id';
    protected $guarded = ['like_recipe_id'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'recipe_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
