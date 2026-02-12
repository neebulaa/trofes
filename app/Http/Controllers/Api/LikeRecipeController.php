<?php

namespace App\Http\Controllers\Api;

use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LikeRecipeController extends Controller
{
    /**
     * Like a recipe
     * 
     * @param Request $request
     * @param Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        // Check if already liked
        $alreadyLiked = $user->likedRecipes()->where('recipes.recipe_id', $recipe->recipe_id)->exists();

        if ($alreadyLiked) {
            return response()->json([
                'success' => false,
                'message' => 'You have already liked this recipe',
            ], 400);
        }

        $user->likedRecipes()->syncWithoutDetaching([
            $recipe->getKey() => ['liked_at' => now()],
        ]);

        $recipe->loadCount('likes');
        
        return response()->json([
            'success' => true,
            'message' => 'Recipe liked successfully',
            'data' => [
                'is_liked' => true,
                'likes_count' => $recipe->likes_count,
            ]
        ]);
    }

    /**
     * Unlike a recipe
     * 
     * @param Request $request
     * @param Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Recipe $recipe)
    {
        $user = $request->user();
        
        // Check if liked
        $isLiked = $user->likedRecipes()->where('recipes.recipe_id', $recipe->recipe_id)->exists();

        if (!$isLiked) {
            return response()->json([
                'success' => false,
                'message' => 'You have not liked this recipe yet',
            ], 400);
        }

        $user->likedRecipes()->detach($recipe->getKey());
        $recipe->loadCount('likes');

        return response()->json([
            'success' => true,
            'message' => 'Recipe unliked successfully',
            'data' => [
                'is_liked' => false,
                'likes_count' => $recipe->likes_count,
            ]
        ]);
    }
}