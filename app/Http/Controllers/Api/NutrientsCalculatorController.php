<?php

namespace App\Http\Controllers\Api;

use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NutrientsCalculatorController extends Controller
{
    /**
     * Get calculator initial data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user_age = null;
        
        if ($request->user() && $request->user()->birth_date) {
            $user_age = Carbon::parse($request->user()->birth_date)->age;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_age' => $user_age,
                'activity_levels' => [
                    'LOW' => 'Sedentary (little or no exercise)',
                    'MIDDLE' => 'Lightly active (exercise 1-3 days/week)',
                    'HIGH' => 'Moderately active (exercise 3-5 days/week)',
                    'VERY HIGH' => 'Very active (exercise 6-7 days/week)',
                ],
                'goals' => [
                    'LOSE' => 'Lose weight',
                    'MAINTAIN' => 'Maintain weight',
                    'GAIN' => 'Gain weight',
                    'MUSCLE' => 'Build muscle',
                ]
            ]
        ]);
    }

    /**
     * Calculate BMR (Basal Metabolic Rate)
     * Using Mifflin-St Jeor Equation
     */
    private function calculateBMR($weight, $height, $age, $gender)
    {
        if ($gender === 'male') {
            return (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
        } else {
            return (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
        }
    }

    /**
     * Get activity multiplier
     */
    private function getActivityMultiplier($activityLevel)
    {
        $multipliers = [
            'LOW' => 1.2,
            'MIDDLE' => 1.375,
            'HIGH' => 1.55,
            'VERY HIGH' => 1.725,
        ];

        return $multipliers[$activityLevel] ?? 1.2;
    }

    /**
     * Adjust calories based on goal
     */
    private function adjustCaloriesForGoal($tdee, $goal)
    {
        switch ($goal) {
            case 'LOSE':
                return $tdee - 500; // Deficit for weight loss
            case 'GAIN':
                return $tdee + 500; // Surplus for weight gain
            case 'MUSCLE':
                return $tdee + 300; // Slight surplus for muscle building
            case 'MAINTAIN':
            default:
                return $tdee; // Maintenance
        }
    }

    /**
     * Calculate macros distribution
     */
    private function calculateMacros($calories, $goal)
    {
        // Default macro split (percentage)
        $macroSplit = [
            'LOSE' => ['protein' => 0.40, 'carbs' => 0.30, 'fat' => 0.30],
            'MAINTAIN' => ['protein' => 0.30, 'carbs' => 0.40, 'fat' => 0.30],
            'GAIN' => ['protein' => 0.30, 'carbs' => 0.50, 'fat' => 0.20],
            'MUSCLE' => ['protein' => 0.35, 'carbs' => 0.45, 'fat' => 0.20],
        ];

        $split = $macroSplit[$goal] ?? $macroSplit['MAINTAIN'];

        // Calculate grams (1g protein = 4 cal, 1g carbs = 4 cal, 1g fat = 9 cal)
        return [
            'protein_g' => round(($calories * $split['protein']) / 4),
            'carbs_g' => round(($calories * $split['carbs']) / 4),
            'fat_g' => round(($calories * $split['fat']) / 9),
            'protein_pct' => round($split['protein'] * 100),
            'carbs_pct' => round($split['carbs'] * 100),
            'fat_pct' => round($split['fat'] * 100),
        ];
    }

    /**
     * Find recipe recommendations based on nutrients
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findRecommendation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'age' => 'required|integer|min:0|max:120',
            'gender' => 'required|string|in:male,female,other',
            'weight' => 'required|numeric|min:1|max:500',
            'height' => 'required|numeric|min:30|max:300',
            'activity_level' => 'required|string|in:LOW,MIDDLE,HIGH,VERY HIGH',
            'goal' => 'required|string|in:LOSE,MAINTAIN,GAIN,MUSCLE',
        ], [
            'age.required' => 'Age is required',
            'age.integer' => 'Age must be an integer',
            'age.min' => 'Age must be at least 0',
            'age.max' => 'Age must not exceed 120',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender must be male, female, or other',
            'weight.required' => 'Weight is required',
            'weight.numeric' => 'Weight must be a number',
            'weight.min' => 'Weight must be at least 1 kg',
            'weight.max' => 'Weight must not exceed 500 kg',
            'height.required' => 'Height is required',
            'height.numeric' => 'Height must be a number',
            'height.min' => 'Height must be at least 30 cm',
            'height.max' => 'Height must not exceed 300 cm',
            'activity_level.required' => 'Activity level is required',
            'activity_level.in' => 'Invalid activity level',
            'goal.required' => 'Goal is required',
            'goal.in' => 'Invalid goal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Extract data
        $age = $validated['age'];
        $gender = $validated['gender'] === 'other' ? 'female' : $validated['gender'];
        $weight = $validated['weight'];
        $height = $validated['height'];
        $activityLevel = $validated['activity_level'];
        $goal = $validated['goal'];

        // Calculate BMR
        $bmr = $this->calculateBMR($weight, $height, $age, $gender);

        // Calculate TDEE (Total Daily Energy Expenditure)
        $activityMultiplier = $this->getActivityMultiplier($activityLevel);
        $tdee = $bmr * $activityMultiplier;

        // Adjust for goal
        $targetCalories = $this->adjustCaloriesForGoal($tdee, $goal);

        // Calculate macros
        $macros = $this->calculateMacros($targetCalories, $goal);

        // Find matching recipes (within tolerance range)
        $caloriesPerMeal = $targetCalories / 3; // Assuming 3 meals per day
        $tolerance = 200; // ±200 calories tolerance

        $is_login = auth('sanctum')->check();
        
        $recipesQuery = Recipe::query()
            ->whereBetween('calories', [
                max(0, $caloriesPerMeal - $tolerance),
                $caloriesPerMeal + $tolerance
            ])
            ->withCount('likes')
            ->when($is_login, function ($q) {
                $q->withExists([
                    'likes as liked_by_me' => fn($qq) => $qq->where('user_id', auth()->id()),
                ]);
            });

        // Filter by user's dietary preferences and allergies if logged in
        if ($is_login) {
            $user = auth()->user();
            
            // Exclude allergies
            $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();
            if (!empty($userAllergyIds)) {
                $recipesQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                    $q->whereIn('allergies.allergy_id', $userAllergyIds);
                });
            }

            // Match dietary preferences
            $userDietIds = $user->dietaryPreferences->pluck('dietary_preference_id')->toArray();
            if (!empty($userDietIds)) {
                $recipesQuery->whereHas('dietaryPreferences', function ($q) use ($userDietIds) {
                    $q->whereIn('dietary_preferences.dietary_preference_id', $userDietIds);
                });
            }
        }

        $recommended_recipes = $recipesQuery->inRandomOrder()->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'calculations' => [
                    'bmr' => round($bmr),
                    'tdee' => round($tdee),
                    'target_calories' => round($targetCalories),
                    'calories_per_meal' => round($caloriesPerMeal),
                    'macros' => $macros,
                ],
                'recommended_recipes' => $recommended_recipes,
                'input_data' => $validated,
            ],
            'message' => 'Based on your data, you need approximately ' . round($targetCalories) . ' calories per day to ' . strtolower(str_replace('_', ' ', $goal)) . '.'
        ]);
    }
}