<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    public function index(Request $request){
        return inertia('Recipes');
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
