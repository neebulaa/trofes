<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Recipe;
use App\Models\Allergy;
use App\Models\Ingredient;
use App\Models\LikeRecipe;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecipeController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        // $perPage = $request->query('per_page', 16);
        $perPage = (int) $request->query('per_page', 16);
        $query = Recipe::query()->withCount('likes');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('measured_ingredients', 'like', "%{$search}%")
                ->orWhere('instructions', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $maxLikes = Recipe::query()
                ->withCount('likes')
                ->orderByDesc('likes_count')
                ->value('likes_count') ?? 0;

        $recipes = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        $recipes->getCollection()->transform(function ($recipe) use ($maxLikes) {
            $recipe->is_favorite = ($maxLikes > 0) && ((int) $recipe->likes_count === (int) $maxLikes);
            return $recipe;
        });

        return Inertia::render('Recipes', [
            'recipes' => $recipes,
            "hero_recipes" => Recipe::inRandomOrder()->limit(5)->get(),
            'recommended_recipes' => Recipe::inRandomOrder()->limit(4)->get(),
        ]);
    }

    public function show(Recipe $recipe){
        return Inertia::render('RecipeDetail', [
            'recipe' => $recipe->loadCount('likes'),
            'user' => Auth::user(),
        ]);
    }

    public function customSearchRecipes(Request $request){
        return inertia('CustomSearchRecipes', [
            'allergies' => Allergy::all(),
            'dietary_preferences' => DietaryPreference::all(),
            'user_allergies' => Auth::user()->allergies->pluck('allergy_id')->toArray(),
            'user_dietary_preferences' => Auth::user()->dietaryPreferences->pluck('dietary_preference_id')->toArray(),
            'user' => Auth::user(), 
            'ingredients' => Ingredient::all()
        ]);
    }
}
