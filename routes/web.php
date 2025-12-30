<?php

use App\Http\Controllers\GuideController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OnboardingController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function(){
    return Inertia::render('Home');
})->name('home');

Route::get('/search', function(){
    return Inertia::render('Searching');
});


Route::get('/contact-us', function(){
    return Inertia::render('ContactUs');
});

Route::middleware('auth')->group(function(){
    // guides
    Route::get('/guides/{guide}', [GuideController::class, 'show']);
    Route::get('/guides', [GuideController::class, 'index']);

    // profile
    Route::get('/profile', [UserController::class, 'index']);
    Route::put('/profile/update', [UserController::class, 'update']);
    Route::post('/profile/update-profile-image', [UserController::class, 'updateProfileImage']);
    Route::delete('/profile/remove-profile-image', [UserController::class, 'removeProfileImage']);

    // onboarding
    Route::get('/onboarding', [OnboardingController::class, 'onboarding']);
    Route::post('/onboarding/profile-setup', [OnboardingController::class, 'setupProfile']);
    Route::post('/onboarding/dietary-preferences-setup', [OnboardingController::class, 'setupDietaryPreferences']);
    Route::post('/onboarding/allergies-setup', [OnboardingController::class, 'setupAllergies']);

    // logout / sign out
    Route::post('/sign-out', [AuthController::class, 'signOut']);
});

Route::middleware('guest')->group(function(){
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::get('/sign-up', [AuthController::class, 'signup']);
    Route::post('/sign-up', [AuthController::class, 'register']);
});

Route::fallback(function(){
    return Inertia::render('NotFound');
});