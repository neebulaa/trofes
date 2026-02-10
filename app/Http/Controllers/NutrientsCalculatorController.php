<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NutrientsCalculatorController extends Controller
{
    public function index(){
        // get user age by using the birth_date attribute in users table
        $user_age = null;
        if(auth()->check() && auth()->user()->birth_date){
            $user_age = Carbon::parse(auth()->user()->birth_date)->age;
        }

        return Inertia::render('NutrientsCalculator', [
            "user_age" => $user_age,
        ]);
    }

    private function getCustomAIRecommendation(){
        return [];
    }

    public function findRecommendation(Request $request){
        $data = $request->validate([
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|string|in:male,female,other',
            'weight' => 'required|numeric|min:1|max:500',
            'height' => 'required|numeric|min:30|max:300',
            'activity_level' => 'required|string|in:LOW,MIDDLE,HIGH,VERY HIGH',
            'goal' => 'required|string|in:LOSE,MAINTAIN,GAIN,MUSCLE',
        ]);

        $age = $data['age'];
        $gender = $data['gender'];
        $weight = $data['weight'];
        $height = $data['height'];
        $activity_level = $data['activity_level'];
        $goal = $data['goal'];

        $calories = $request->calories ?? null;
        $carbs_g = $request->carbs_g ?? null;
        $protein_g = $request->protein_g ?? null;
        $fat_g = $request->fat_g ?? null;
        $carbs_pct = $request->carbs_pct ?? null;
        $protein_pct = $request->protein_pct ?? null;
        $fat_pct = $request->fat_pct ?? null;

        // if is login then take the user and its allergies and dietary preferences
        $is_login = auth()->check();
        $allergies = [];
        $dietary_preferences = [];
        $user = null;
        if($is_login) {
            $user = auth()->user();
            $allergies = $user->allergies()->pluck('allergies.allergy_id')->toArray();
            $dietary_preferences = $user->dietaryPreferences()->pluck('dietary_preferences.dietary_preference_id')->toArray();
        }

        $recommended_recipes = $this->getCustomAIRecommendation();

        if ($recommended_recipes != null) {
            $recommended_recipes = Recipe::query()
                ->withCount('likes')
                ->when($is_login, function ($q) {
                    $q->withExists([
                        'likes as liked_by_me' => fn ($qq) => $qq->where('user_id', auth()->id()),
                    ]);
                })
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }

        // return the same page with the validated data
        return Inertia::render('NutrientsCalculator', [
            'recommended_recipes' => $recommended_recipes,
            'input_data' => $data,
        ]);
    }
}
