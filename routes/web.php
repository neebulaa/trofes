<?php

use App\Http\Controllers\GuideController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OnboardingController;
use App\Models\Guide;
use App\Models\Recipe;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\DashboardController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function(){
    return Inertia::render('Home', [
        'guides' => Guide::all()->take(3),
        'recipes' => Recipe::inRandomOrder()->limit(5)->get(),
    ]);
})->name('home');

Route::get('/contact-us', function(){
    return Inertia::render('ContactUs');
});

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('is_admin')->name('dashboard.index');
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