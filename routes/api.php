<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\YoutubeController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\LikeRecipeController;
use App\Http\Controllers\Api\AllergyController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\DietaryPreferenceController;
use App\Http\Controllers\Api\NutrientsCalculatorController;
use App\Http\Controllers\Api\RoleManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| Base URL: /api/v1/
| Authentication: Laravel Sanctum (Bearer Token)
|
*/

// ============================================================================
// PUBLIC ROUTES (Guest - No Authentication Required)
// ============================================================================

Route::prefix('v1')->group(function () {
    // ------------------------------------------------------------------------
    // Home / Landing
    // ------------------------------------------------------------------------
    Route::get('/home', function() {
        return response()->json([
            'success' => true,
            'data' => [
                'guides' => \App\Models\Guide::latest()->take(3)->get(),
                'recommended_recipes' => \App\Models\Recipe::with(['dietaryPreferences', 'likes'])
                    ->withCount('likes') // Wajib ada agar likes_count bisa dibaca
                    ->inRandomOrder() 
                    ->limit(5)
                    ->get(),

                'popular_recipes' => \App\Models\Recipe::with(['dietaryPreferences', 'likes'])
                    ->withCount('likes') // Menghitung jumlah likes untuk kolom likes_count
                    ->orderBy('likes_count', 'desc')
                    ->take(20)
                    ->get(),
            ]
        ]);
    });

    // ------------------------------------------------------------------------
    // Authentication Routes
    // ------------------------------------------------------------------------
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Google OAuth
    Route::post('/auth/google', [GoogleAuthController::class, 'loginWithGoogle']);
    Route::post('/auth/google/token', [GoogleAuthController::class, 'loginWithGoogleAccessToken']);

    // ------------------------------------------------------------------------
    // Contact Us
    // ------------------------------------------------------------------------
    Route::post('/contact-us', [MessageController::class, 'store']);

    // ------------------------------------------------------------------------
    // Nutrients Calculator (Public Access)
    // ------------------------------------------------------------------------
    Route::get('/nutrients-calculator', [NutrientsCalculatorController::class, 'index']);
    Route::post('/nutrients-calculator', [NutrientsCalculatorController::class, 'findRecommendation']);

    // ------------------------------------------------------------------------
    // Public Resources (untuk reference data)
    // ------------------------------------------------------------------------
    Route::get('/allergies', [AllergyController::class, 'index']);
    Route::get('/dietary-preferences', [DietaryPreferenceController::class, 'index']);
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/popular', [IngredientController::class, 'popular']);
});

// ============================================================================
// PROTECTED ROUTES (Requires Authentication - Sanctum Token)
// ============================================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // ------------------------------------------------------------------------
    // Auth User Management
    // ------------------------------------------------------------------------
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Google Account
    Route::delete('/auth/google/unlink', [GoogleAuthController::class, 'unlinkGoogle']);

    // ------------------------------------------------------------------------
    // Onboarding Process
    // ------------------------------------------------------------------------
    Route::prefix('onboarding')->group(function() {
        Route::get('/status', [OnboardingController::class, 'status']);
        Route::post('/profile-setup', [OnboardingController::class, 'setupProfile']);
        Route::post('/dietary-preferences-setup', [OnboardingController::class, 'setupDietaryPreferences']);
        Route::post('/allergies-setup', [OnboardingController::class, 'setupAllergies']);
        Route::post('/complete', [OnboardingController::class, 'completeOnboarding']);
    });

    // ------------------------------------------------------------------------
    // Routes that require completed onboarding
    // ------------------------------------------------------------------------
    Route::middleware('onboarded')->group(function() {
        
        // --------------------------------------------------------------------
        // Guides Routes
        // --------------------------------------------------------------------
        Route::get('/guides', [GuideController::class, 'index']);
        Route::get('/guides/{guide}', [GuideController::class, 'show']);

        // --------------------------------------------------------------------
        // Recipes Routes
        // --------------------------------------------------------------------
        Route::get('/recipes', [RecipeController::class, 'index']);
        Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
        
        // Custom Search Recipes
        Route::post('/recipes/custom-search', [RecipeController::class, 'customSearch']);

        // Like/Unlike Recipe
        Route::post('/recipes/{recipe}/like', [LikeRecipeController::class, 'store']);
        Route::delete('/recipes/{recipe}/like', [LikeRecipeController::class, 'destroy']);

        // --------------------------------------------------------------------
        // User Profile Routes
        // --------------------------------------------------------------------
        Route::get('/profile', [UserController::class, 'show']);
        Route::put('/profile/update', [UserController::class, 'update']);
        Route::post('/profile/update-image', [UserController::class, 'updateProfileImage']);
        Route::delete('/profile/remove-image', [UserController::class, 'removeProfileImage']);

        // --------------------------------------------------------------------
        // YouTube Search
        // --------------------------------------------------------------------
        Route::get('/youtube/search', [YoutubeController::class, 'search']);

        // --------------------------------------------------------------------
        // Resource Detail Routes
        // --------------------------------------------------------------------
        Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);
        Route::get('/allergies/{allergy}', [AllergyController::class, 'show']);
        Route::get('/dietary-preferences/{dietary_preference}', [DietaryPreferenceController::class, 'show']);
    });

    // ========================================================================
    // ADMIN ROUTES (Requires Admin Role)
    // ========================================================================
    
    Route::middleware('is_admin')->prefix('admin')->group(function() {
        
        
        // --------------------------------------------------------------------
        // Dashboard
        // --------------------------------------------------------------------
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/analytics', [DashboardController::class, 'analytics']);

        // --------------------------------------------------------------------
        // Dashboard - Guides Management
        // --------------------------------------------------------------------
        Route::get('/guides', [GuideController::class, 'adminIndex']);
        Route::post('/guides', [GuideController::class, 'store']);
        Route::get('/guides/{guide}', [GuideController::class, 'show']);
        Route::put('/guides/{guide}', [GuideController::class, 'update']);
        Route::delete('/guides/{guide}', [GuideController::class, 'destroy']);

        // --------------------------------------------------------------------
        // Dashboard - Allergies Management
        // --------------------------------------------------------------------
        Route::get('/allergies', [AllergyController::class, 'index']);
        Route::post('/allergies', [AllergyController::class, 'store']);
        Route::get('/allergies/{allergy}', [AllergyController::class, 'show']);
        Route::put('/allergies/{allergy}', [AllergyController::class, 'update']);
        Route::delete('/allergies/{allergy}', [AllergyController::class, 'destroy']);

        // --------------------------------------------------------------------
        // Dashboard - Dietary Preferences Management
        // --------------------------------------------------------------------
        Route::get('/dietary-preferences', [DietaryPreferenceController::class, 'index']);
        Route::post('/dietary-preferences', [DietaryPreferenceController::class, 'store']);
        Route::get('/dietary-preferences/{dietary_preference}', [DietaryPreferenceController::class, 'show']);
        Route::put('/dietary-preferences/{dietary_preference}', [DietaryPreferenceController::class, 'update']);
        Route::delete('/dietary-preferences/{dietary_preference}', [DietaryPreferenceController::class, 'destroy']);

        // --------------------------------------------------------------------
        // Dashboard - Ingredients Management
        // --------------------------------------------------------------------

        Route::post('/ingredients', [IngredientController::class, 'store']);
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);

        // --------------------------------------------------------------------
        // Dashboard - Messages Management
        // --------------------------------------------------------------------
        Route::get('/messages', [MessageController::class, 'index']);
        Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

        // --------------------------------------------------------------------
        // Dashboard - Role Management
        // --------------------------------------------------------------------
        Route::get('/roles', [RoleManagementController::class, 'index']);
        Route::post('/roles/assign', [RoleManagementController::class, 'assign']);
    });
});

// ============================================================================
// Fallback Route (404 Handler)
// ============================================================================
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error' => 'The requested resource does not exist'
    ], 404);
});