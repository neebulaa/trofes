<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Guide;
use App\Models\Recipe;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $totalRecipes = Recipe::count();
        $totalGuides = Guide::count();
        $totalMessages = Message::count();
        
        // New users this month
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // New users today
        $newUsersToday = User::whereDate('created_at', today())->count();
        
        // Most liked recipes
        $mostLikedRecipes = Recipe::withCount('likes')
            ->orderByDesc('likes_count')
            ->limit(5)
            ->get(['recipe_id', 'title', 'slug', 'image']);
        
        // Recent users
        $recentUsers = User::latest()
            ->limit(10)
            ->get(['user_id', 'username', 'email', 'full_name', 'created_at', 'role']);
        
        // Unread messages count
        $unreadMessages = Message::whereNull('read_at')->count();
        
        // User growth per month (last 6 months)
        $userGrowth = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Recipes by dietary preference
        $recipesByDiet = DB::table('recipe_dietary_preference')
            ->select('dietary_preferences.diet_name', DB::raw('COUNT(*) as count'))
            ->join('dietary_preferences', 'recipe_dietary_preference.dietary_preference_id', '=', 'dietary_preferences.dietary_preference_id')
            ->groupBy('dietary_preferences.diet_name')
            ->get();

        // Total likes
        $totalLikes = DB::table('like_recipes')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_users' => $totalUsers,
                    'total_recipes' => $totalRecipes,
                    'total_guides' => $totalGuides,
                    'total_messages' => $totalMessages,
                    'new_users_this_month' => $newUsersThisMonth,
                    'new_users_today' => $newUsersToday,
                    'unread_messages' => $unreadMessages,
                    'total_likes' => $totalLikes,
                ],
                'most_liked_recipes' => $mostLikedRecipes,
                'recent_users' => $recentUsers,
                'user_growth' => $userGrowth,
                'recipes_by_diet' => $recipesByDiet,
            ]
        ]);
    }

    /**
     * Get dashboard analytics (detailed)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function analytics()
    {
        // Recipe statistics
        $recipeStats = [
            'total' => Recipe::count(),
            'avg_cooking_time' => round(Recipe::avg('cooking_time'), 2),
            'avg_calories' => round(Recipe::avg('calories'), 2),
            'avg_protein' => round(Recipe::avg('protein'), 2),
            'avg_fat' => round(Recipe::avg('fat'), 2),
            'avg_sodium' => round(Recipe::avg('sodium'), 2),
            'total_likes' => DB::table('like_recipes')->count(),
            'avg_likes_per_recipe' => round(DB::table('like_recipes')->count() / max(Recipe::count(), 1), 2),
        ];

        // User statistics
        $userStats = [
            'total' => User::count(),
            'with_allergies' => User::has('allergies')->count(),
            'with_dietary_prefs' => User::has('dietaryPreferences')->count(),
            'completed_onboarding' => User::where('onboarding_completed', true)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'verified_emails' => User::whereNotNull('email_verified_at')->count(),
        ];

        // Guide statistics
        $guideStats = [
            'total' => Guide::count(),
            'published_this_month' => Guide::whereMonth('published_at', now()->month)
                ->whereYear('published_at', now()->year)
                ->count(),
            'published_today' => Guide::whereDate('published_at', today())->count(),
        ];

        // Message statistics
        $messageStats = [
            'total' => Message::count(),
            'unread' => Message::whereNull('read_at')->count(),
            'today' => Message::whereDate('created_at', today())->count(),
        ];

        // Top ingredients
        $topIngredients = DB::table('recipe_ingredient')
            ->select('ingredients.ingredient_name', DB::raw('COUNT(*) as usage_count'))
            ->join('ingredients', 'recipe_ingredient.ingredient_id', '=', 'ingredients.ingredient_id')
            ->groupBy('ingredients.ingredient_name')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        // Top allergies (most common among users)
        $topAllergies = DB::table('user_allergy')
            ->select('allergies.allergy_name', DB::raw('COUNT(*) as user_count'))
            ->join('allergies', 'user_allergy.allergy_id', '=', 'allergies.allergy_id')
            ->groupBy('allergies.allergy_name')
            ->orderByDesc('user_count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recipe_stats' => $recipeStats,
                'user_stats' => $userStats,
                'guide_stats' => $guideStats,
                'message_stats' => $messageStats,
                'top_ingredients' => $topIngredients,
                'top_allergies' => $topAllergies,
            ]
        ]);
    }
}

      