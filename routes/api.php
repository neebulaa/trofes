<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuideController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function(){
    // guide
    Route::get("/guides", [GuideController::class, 'index']);
    Route::get("/guides/{guide}", [GuideController::class, 'show']);
    Route::post('/guides', [GuideController::class, 'store']);
    Route::put('/guides/{guide}', [GuideController::class, 'update']);
    Route::delete('/guides/{guide}', [GuideController::class, 'destroy']);
});
