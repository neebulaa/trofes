<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
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

Route::get('/guides', function(){
    return Inertia::render('Guides');
});

Route::get('/contact-us', function(){
    return Inertia::render('ContactUs');
});

Route::middleware('auth')->group(function(){
    Route::get('/profile', [UserController::class, 'index']);
    Route::put('/profile/update', [UserController::class, 'update']);
    Route::get('/onboarding', [OnboardingController::class, 'onboarding']);
    Route::post('/onboarding/profile-setup', [OnboardingController::class, 'setupProfile']);
    Route::post('/onboarding/dietary-preferences-setup', [OnboardingController::class, 'setupDietaryPreferences']);
    Route::post('/onboarding/allergies-setup', [OnboardingController::class, 'setupAllergies']);
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