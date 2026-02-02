<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Recipe;

class RecipeController extends Controller
{
    public function index(){
        $recipes = Recipe::paginate(9);
        
        return response()->json([
            "status" => 200,
            "message" => "Fetch all recipes",
            "data" => $recipes
        ]);
    }
}
