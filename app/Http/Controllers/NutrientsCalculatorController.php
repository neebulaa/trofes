<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'recommended_recipes' => Recipe::inRandomOrder()->limit(5)->get(),
            "user_age" => $user_age,
        ]);
    }

    private function getCustomAIRecommendation(
        $is_login,
        $calories,
        $carbs_g,
        $protein_g,
        $fat_g,
        $allergies,
        $dietary_preferences
    )
    {
        // dd($is_login,
        // $calories,
        // $carbs_g,
        // $protein_g,
        // $fat_g,
        // $allergies,
        // $dietary_preferences);
        try{
            // dd([
            //     'calories_sent' => $calories,
            //     'protein_sent' => $protein_g,
            //     'fat_sent' => $fat_g,
            //     'carbs_sent' => $carbs_g
            // ]);
            $apiUrl = 'https://arnight-trofes-api.hf.space/recommendCalculator';
            $response = Http::withoutVerifying()
            ->timeout(25)
            ->withHeaders(['Accept' => 'application/json'])
            ->post($apiUrl, [
                'is_login' => filter_var($is_login, FILTER_VALIDATE_BOOLEAN),
                'calories' => (float) $calories,
                'carbs' => (float) $carbs_g,
                'protein' => (float) $protein_g,
                'fat' => (float) $fat_g,
                'top_k' => 10,
                'is_start_from_zero' => false,
                'allergy_ids' => array_values($allergies),
                'dietary_ids' => array_values($dietary_preferences),
            ]);

            // dd($response);

            if ($response->successful()) {
                $ids = $response->json('recommended_ids');
                // dd($ids);
                if (empty($ids)) return [];

                // Ambil data resep dari database sesuai urutan kemiripan dari AI
                return Recipe::whereIn('recipe_id', $ids)
                    ->orderByRaw("FIELD(recipe_id, " . implode(',', $ids) . ")")
                    ->get();
            }
            // dd("not success");
            Log::error('AI API Response Error: ' . $response->body());
            return [];
        }
        catch (\Exception $e) {
            // Log the error or handle it as needed
            Log::error('Error fetching AI recommendations: ' . $e->getMessage());
            // dd([
            //     'PESAN_ERROR' => $e->getMessage(),
            //     'KELAS_EXCEPTION' => get_class($e),
            //     'BARIS' => $e->getLine()
            // ]);
            return [];
        }
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
            'calories' => 'required|numeric',
            'carbs_g' => 'required|numeric',
            'protein_g' => 'required|numeric',
            'fat_g' => 'required|numeric',
        ]);

        $age = $data['age'];
        $gender = $data['gender'];
        $weight = $data['weight'];
        $height = $data['height'];
        $activity_level = $data['activity_level'];
        $goal = $data['goal'];
        
        $daily_calories = $data['calories'];
        $daily_carbs = $data['carbs_g'];
        $daily_protein = $data['protein_g'];
        $daily_fat = $data['fat_g'];
        $carbs_pct = $request->carbs_pct ?? null;
        $protein_pct = $request->protein_pct ?? null;
        $fat_pct = $request->fat_pct ?? null;

        // --- LOGIKA PEMBAGIAN PORSI ---
        $meal_divider = 3;
        
        $target_calories = $daily_calories / $meal_divider;
        $target_carbs = $daily_carbs / $meal_divider;
        $target_protein = $daily_protein / $meal_divider;
        $target_fat = $daily_fat / $meal_divider;
        
        // dd($calories, $carbs_g, $protein_g, $fat_g, $carbs_pct, $protein_pct, $fat_pct);
        // if is login then take the user and its allergies and dietary preferences
        $is_login = auth()->check();
        $allergies = [];
        $dietary_preferences = [];
        $user = null;
        if($is_login) {
            $user = auth()->user();
            $allergies = $user->allergies()
            ->pluck('user_allergies.allergy_id')
            ->map(fn($id) => (int)$id) // Pastikan ID adalah integer
            ->toArray();
            
            $dietary_preferences = $user->dietaryPreferences()
            ->pluck('user_dietary_preferences.dietary_preference_id')
            ->map(fn($id) => (int)$id)
            ->toArray();
        }

        $recommended_recipes = $this->getCustomAIRecommendation(
            $is_login,
            $target_calories,
            $target_carbs,
            $target_protein,
            $target_fat,
            $allergies,
            $dietary_preferences
        );

        if (empty($recommended_recipes) || (is_object($recommended_recipes) && $recommended_recipes->isEmpty())) {
            // dd("its wrong bro");
            $recommended_recipes = Recipe::inRandomOrder()->limit(5)->get();
        }

        // return the same page with the validated data
        return Inertia::render('NutrientsCalculator', [
            'recommended_recipes' => $recommended_recipes,
            'input_data' => $data,
        ]);
    }
}
