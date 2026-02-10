<?php

use Inertia\Inertia;
use App\Models\Guide;
use App\Models\Recipe;
use App\Models\LikeRecipe;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\YoutubeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Api\LikeRecipeController;
use App\Http\Controllers\DashboardGuideController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardAllergyController;
use App\Http\Controllers\NutrientsCalculatorController;
use App\Http\Controllers\DashboardRoleManagementController;
use App\Http\Controllers\DashboardDietaryPreferenceController;
use App\Http\Controllers\DashboardMessageController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function(){
    return Inertia::render('Home', [
        'guides' => Guide::all()->take(3),
        'recipes' => Recipe::inRandomOrder()->limit(5)->get(),
    ]);
})->name('home');

Route::get('/contact-us', [MessageController::class, 'index']);
Route::post('/contact-us', [MessageController::class, 'store']);

Route::get('/nutrients-calculator', [NutrientsCalculatorController::class, 'index'])->name('nutrients-calculator');
Route::post('/nutrients-calculator', [NutrientsCalculatorController::class, 'findRecommendation']);

Route::middleware('auth')->group(function(){

    Route::middleware('onboarded')->group(function(){
        // guides
        Route::get('/guides/{guide}', [GuideController::class, 'show']);
        Route::get('/guides', [GuideController::class, 'index']);
    
        // profile
        Route::get('/profile', [UserController::class, 'index']);
        Route::put('/profile/update', [UserController::class, 'update']);
        Route::post('/profile/update-profile-image', [UserController::class, 'updateProfileImage']);
        Route::delete('/profile/remove-profile-image', [UserController::class, 'removeProfileImage']);

        // recipes
        Route::get('/recipes', [RecipeController::class, 'index']);
        Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
        Route::get('/custom-search-recipes', [RecipeController::class, 'customSearchRecipes']);
        Route::post('/custom-search-recipes', [RecipeController::class, 'performCustomSearchRecipes']);

        // like recipe
        Route::post('/recipes/{recipe}/like', [LikeRecipeController::class, 'store']);
        Route::delete('/recipes/{recipe}/like', [LikeRecipeController::class, 'destroy']);
    });

    // onboarding
    Route::get('/onboarding', [OnboardingController::class, 'onboarding'])->name('onboarding.index');
    Route::post('/onboarding/profile-setup', [OnboardingController::class, 'setupProfile']);
    Route::post('/onboarding/dietary-preferences-setup', [OnboardingController::class, 'setupDietaryPreferences']);
    Route::post('/onboarding/allergies-setup', [OnboardingController::class, 'setupAllergies']);
    Route::post('/onboarding/complete', [OnboardingController::class, 'completeOnboarding']);
    
    // logout / sign out
    Route::post('/sign-out', [AuthController::class, 'signOut']);

    // dashboard
    Route::middleware('is_admin')->group(function(){
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // dashboard guides
        Route::get('/dashboard/guides', [DashboardGuideController::class, 'index']);
        Route::get('/dashboard/guides/create', [DashboardGuideController::class, 'create']);
        Route::post('/dashboard/guides', [DashboardGuideController::class, 'store']);
        Route::get('/dashboard/guides/{guide}', [DashboardGuideController::class, 'show']);
        Route::get('/dashboard/guides/{guide}/edit', [DashboardGuideController::class, 'edit']);
        Route::put('/dashboard/guides/{guide}', [DashboardGuideController::class, 'update']);
        Route::delete('/dashboard/guides/{guide}', [DashboardGuideController::class, 'destroy']);

        // dashboard allergies
        Route::get("/dashboard/allergies", [DashboardAllergyController::class, 'index']);
        Route::get("/dashboard/allergies/create", [DashboardAllergyController::class, 'create']);
        Route::post("/dashboard/allergies", [DashboardAllergyController::class, 'store']);
        Route::get("/dashboard/allergies/{allergy}/edit", [DashboardAllergyController::class, 'edit']);
        Route::put("/dashboard/allergies/{allergy}", [DashboardAllergyController::class, 'update']);
        Route::delete("/dashboard/allergies/{allergy}", [DashboardAllergyController::class, 'destroy']);

        // dashboard dietary preferences
        Route::get("/dashboard/dietary-preferences", [DashboardDietaryPreferenceController::class, 'index']);
        Route::get("/dashboard/dietary-preferences/create", [DashboardDietaryPreferenceController::class, 'create']);
        Route::post("/dashboard/dietary-preferences", [DashboardDietaryPreferenceController::class, 'store']);
        Route::get("/dashboard/dietary-preferences/{dietary_preference}/edit", [DashboardDietaryPreferenceController::class, 'edit']);
        Route::put("/dashboard/dietary-preferences/{dietary_preference}", [DashboardDietaryPreferenceController::class, 'update']);
        Route::delete("/dashboard/dietary-preferences/{dietary_preference}", [DashboardDietaryPreferenceController::class, 'destroy']);

        // role management
        Route::get('/dashboard/roles', [DashboardRoleManagementController::class, 'index']);
        Route::post('/dashboard/roles/assign', [DashboardRoleManagementController::class, 'assign']);

        // messages
        Route::get('/dashboard/messages', [DashboardMessageController::class, 'index']);
        Route::delete('/dashboard/messages/{message}', [DashboardMessageController::class, 'destroy']);
    });

    // youtube search
    Route::get('/youtube/search', [YoutubeController::class, 'search']);
});

Route::middleware('guest')->group(function(){
    // reset password
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
    
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::get('/sign-up', [AuthController::class, 'signup']);
    Route::post('/sign-up', [AuthController::class, 'register']);

    // oauth google
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('auth.google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::fallback(function(){
    return Inertia::render('NotFound');
});