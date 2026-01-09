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

        return response()->json([
            'is_liked' => true,
            'likes_count' => $recipe->likes()->count(),
        ]);
    }

    public function destroy(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        $user->likedRecipes()->detach($recipe->getKey());

        return response()->json([
            'is_liked' => false,
            'likes_count' => $recipe->likes()->count(),
        ]);
    }
}

