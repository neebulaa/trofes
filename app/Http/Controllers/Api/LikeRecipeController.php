<?php

namespace App\Http\Controllers\Api;

use App\Models\Recipe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LikeRecipeController extends Controller
{
    public function store(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        $user->likedRecipes()->syncWithoutDetaching([
            $recipe->getKey() => ['liked_at' => now()],
        ]);

        $recipe->loadCount('likes');
        
        return response()->json([
            'is_liked' => true,
            'likes_count' => $recipe->likes_count,
        ]);
    }

    public function destroy(Request $request, Recipe $recipe)
    {
        $user = $request->user();
        
        $user->likedRecipes()->detach($recipe->getKey());
        $recipe->loadCount('likes');

        return response()->json([
            'is_liked' => false,
            'likes_count' => $recipe->likes_count,
        ]);
    }
}

