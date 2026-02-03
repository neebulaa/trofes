<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Guide;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\User;
use App\Helpers\ActivityLogFormatter;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index(){
        $latest_activity = ActivityLog::latest()
            ->take(6)
            ->get()
            ->map(function ($log) {
                return ActivityLogFormatter::toText(
                    $log->action,
                    $log->meta? json_decode($log->meta, true) : null
                );
            });

        return Inertia::render('Dashboard/Home', [
            'recipe_count' => Recipe::count(),
            'user_count' => User::count(),
            'ingredient_count' => Ingredient::count(),
            'popular_recipes' => Recipe::withCount('likes')->orderBy('likes_count', 'desc')->take(3)->get(),
            'latest_guides' => Guide::latest()->take(3)->get(),
            'latest_activity' => $latest_activity,
        ]);
    }
}
