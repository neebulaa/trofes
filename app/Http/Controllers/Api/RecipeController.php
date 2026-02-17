<?php

namespace App\Http\Controllers\Api;

use App\Models\Recipe;
use App\Models\Allergy;
use App\Models\Ingredient;
use App\Models\LikeRecipe;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    /**
     * Random rows helper
     */
    private function randomRows($query, int $limit)
    {
        $count = (clone $query)->count();
        if ($count <= $limit) return $query->limit($limit)->get();

        $offset = random_int(0, max(0, $count - $limit));
        return $query->offset($offset)->limit($limit)->get();
    }

    /**
     * Get filter pills (for mobile filtering UI)
     * Returns available filter options based on ingredients, diets, and allergies
     */
    private function getFilterPills(): array
    {
        $pillOptions = [];
        
        $pillOptions[] = [
            'key' => 'all',
            'label' => 'All',
            'type' => 'all',
            'id' => null,
        ];

        $pillOptions[] = [
            'key' => 'popular',
            'label' => 'Popular',
            'type' => 'popular',
            'id' => null,
        ];

        $ingredientPills = $this->randomRows(
            Ingredient::query()->select(['ingredient_id', 'ingredient_name']),
            6
        )->map(fn ($i) => [
            'key' => "ingredient:{$i->ingredient_id}",
            'label' => ucfirst($i->ingredient_name),
            'type' => 'ingredient',
            'id' => $i->ingredient_id,
        ]);

        $dietPills = $this->randomRows(
            DietaryPreference::query()->select(['dietary_preference_id', 'diet_name']),
            6
        )->map(fn ($d) => [
            'key' => "diet:{$d->dietary_preference_id}",
            'label' => $d->diet_name,
            'type' => 'diet',
            'id' => $d->dietary_preference_id,
        ]);

        $allergyPills = $this->randomRows(
            Allergy::query()->select(['allergy_id', 'allergy_name']),
            6
        )->map(fn ($a) => [
            'key' => "no_allergy:{$a->allergy_id}",
            'label' => 'No ' . $a->allergy_name,
            'type' => 'no_allergy',
            'id' => $a->allergy_id,
        ]);

        $random6 = $ingredientPills
            ->concat($dietPills)
            ->concat($allergyPills)
            ->shuffle()
            ->take(6)
            ->values()
            ->all();

        $pillOptions = array_merge($pillOptions, $random6);

        return $pillOptions;
    }

    /**
     * Load recipe relationships for API response
     * 
     * @param \Illuminate\Database\Eloquent\Collection $recipes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function loadRecipeRelationships($recipes)
    {
        return $recipes->load([
            'dietaryPreferences:dietary_preference_id,diet_name', // description dihapus
            'allergies:allergy_id,allergy_name' // description dihapus
    ]);
}

    /**
     * Get AI Recommendation with caching and filtering
     * 
     * @param array $likedRecipeIds User's liked recipe IDs
     * @param int $limit Number of recipes to return
     * @param int|null $userId User ID for cache key
     * @return array ['data' => Collection, 'warning' => string|null]
     */
    private function getAIRecommendationCached(array $likedRecipeIds, int $limit, ?int $userId)
    {
        // If user has no liked recipes, return random recipes
        if (empty($likedRecipeIds)) {
            $recipes = Recipe::select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                ->withCount('likes')
                ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            return [
                'data' => $recipes,
                'warning' => null
            ];
        }

        $user = Auth::user();
        
        // Generate cache key based on user preferences
        $allergyHash = $user ? md5(json_encode($user->allergies->pluck('allergy_id')->sort()->toArray())) : 'no-allergy';
        $dietHash = $user ? md5(json_encode($user->dietaryPreferences->pluck('dietary_preference_id')->sort()->toArray())) : 'no-diet';
        $likeHash = md5(json_encode($likedRecipeIds));
        
        $cacheKey = "ai_rec_v7:u={$userId}:l={$likeHash}:a={$allergyHash}:d={$dietHash}:lim={$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($likedRecipeIds, $limit, $user) {
            try {
                // Call AI Recommendation API
                $response = Http::withoutVerifying()->timeout(5)
                    ->post('https://arnight-trofes-api.hf.space/recommend', [
                        'liked_ids' => $likedRecipeIds,
                        'top_k' => max(100, $limit * 2),
                        'is_start_from_zero' => false,
                    ]);

                if (!$response->successful()) {
                    Log::warning('AI recommend failed', ['status' => $response->status()]);
                    throw new \Exception("AI API Down");
                }

                $recommendedIds = $response->json('recommended_ids') ?? [];

                if (empty($recommendedIds)) {
                    $recipes = Recipe::select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                        ->withCount('likes')
                        ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name,description'])
                        ->inRandomOrder()
                        ->limit($limit)
                        ->get();

                    return [
                        'data' => $recipes,
                        'warning' => null
                    ];
                }

                // Start filtering based on user preferences
                $baseQuery = Recipe::query()
                    ->select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                    ->withCount('likes')
                    ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
                    ->whereIn('recipe_id', $recommendedIds);

                if ($user) {
                    // Filter out recipes with user's allergies
                    $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();
                    if (!empty($userAllergyIds)) {
                        $baseQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                            $q->whereIn('allergies.allergy_id', $userAllergyIds);
                        });
                    }
                }

                // Filter based on dietary preferences
                $warningMessage = null;
                $userDietIds = $user ? $user->dietaryPreferences->pluck('dietary_preference_id')->toArray() : [];
                
                if (!empty($userDietIds)) {
                    // Try to find recipes matching ALL dietary preferences
                    $perfectQuery = (clone $baseQuery);
                    foreach ($userDietIds as $dietId) {
                        $perfectQuery->whereHas('dietaryPreferences', fn($q) => 
                            $q->where('dietary_preferences.dietary_preference_id', $dietId)
                        );
                    }

                    if ($perfectQuery->count() > 0) {
                        $query = $perfectQuery;
                    } else {
                        // If no perfect match, find recipes matching at least one preference
                        $partialQuery = (clone $baseQuery)->whereHas('dietaryPreferences', function ($q) use ($userDietIds) {
                            $q->whereIn('dietary_preferences.dietary_preference_id', $userDietIds);
                        });

                        if ($partialQuery->count() > 0) {
                            $query = $partialQuery;
                            $warningMessage = "We couldn't find recipes matching ALL your diets, so we're showing some that match at least one.";
                        } else {
                            $query = $baseQuery;
                        }
                    }
                } else {
                    $query = $baseQuery;
                }

                // Get results in AI recommendation order
                $idsString = implode(',', $recommendedIds);
                $recommended = $query->orderByRaw("FIELD(recipe_id, $idsString)")
                    ->take($limit)
                    ->get();

                // Fallback if filtered results are less than limit
                if ($recommended->count() < $limit) {
                    $needed = $limit - $recommended->count();
                    
                    $fallbackQuery = Recipe::select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                        ->withCount('likes')
                        ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
                        ->whereNotIn('recipe_id', $recommended->pluck('recipe_id'));
                    
                    if ($user) {
                        // Still exclude allergies in fallback
                        if (!empty($userAllergyIds)) {
                            $fallbackQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                                $q->whereIn('allergies.allergy_id', $userAllergyIds);
                            });
                        }
                        
                        // Still match dietary preferences in fallback
                        if (!empty($userDietIds)) {
                            foreach ($userDietIds as $dietId) {
                                $fallbackQuery->whereHas('dietaryPreferences', fn($q) => 
                                    $q->where('dietary_preferences.dietary_preference_id', $dietId)
                                );
                            }
                        }
                    }
                    
                    $extra = $fallbackQuery->inRandomOrder()->limit($needed)->get();
                    $recommended = $recommended->concat($extra);
                }

                return [
                    'data' => $recommended,
                    'warning' => $warningMessage
                ];

            } catch (\Throwable $e) {
                Log::warning('AI recommend exception', ['msg' => $e->getMessage()]);
                
                $recipes = Recipe::select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                    ->withCount('likes')
                    ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
                    ->inRandomOrder()
                    ->limit($limit)
                    ->get();

                return [
                    'data' => $recipes,
                    'warning' => null
                ];
            }
        });
    }

    /**
     * Get all recipes with AI recommendations
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'filter_type' => 'nullable|string|in:all,popular,ingredient,diet,no_allergy',
            'filter_id' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
        ], [
            'search.string' => 'Search must be a string',
            'search.max' => 'Search query too long',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
            'filter_type.in' => 'Invalid filter type',
            'filter_id.integer' => 'Filter ID must be an integer',
            'filter_id.min' => 'Filter ID must be at least 1',
            'page.integer' => 'Page must be an integer',
            'page.min' => 'Page must be at least 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 16);
        $filterType = $request->query('filter_type');
        $filterId = $request->query('filter_id');

        // Build main query with dietary_preferences and allergies
        $query = Recipe::query()
            ->select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
            ->withCount('likes')
            ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
            ->when($request->user(), function ($q) use ($request) {
                $q->withExists([
                    'likes as liked_by_me' => fn($qq) => $qq->where('user_id', $request->user()->user_id),
                ]);
            });

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('measured_ingredients', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Apply pill filters
        if ($filterType === 'popular') {
            $query->orderByDesc('likes_count');
        } elseif ($filterType === 'ingredient' && $filterId) {
            $query->whereHas('ingredients', fn($q) => 
                $q->where('ingredients.ingredient_id', (int) $filterId)
            );
        } elseif ($filterType === 'diet' && $filterId) {
            $query->whereHas('dietaryPreferences', fn($q) => 
                $q->where('dietary_preferences.dietary_preference_id', (int) $filterId)
            );
        } elseif ($filterType === 'no_allergy' && $filterId) {
            $query->whereDoesntHave('allergies', fn($q) => 
                $q->where('allergies.allergy_id', (int) $filterId)
            );
        }

        // Get max likes for is_favorite flag
        $maxLikes = Recipe::withCount('likes')
            ->orderByDesc('likes_count')
            ->value('likes_count') ?? 0;

        // Paginate results
        $recipes = $query->paginate($perPage);

        // Add is_favorite flag
        $recipes->getCollection()->transform(function ($recipe) use ($maxLikes) {
            $recipe->is_favorite = ($maxLikes > 0) && ((int) $recipe->likes_count === (int) $maxLikes);
            return $recipe;
        });

        // Get filter pills
        $pillOptions = $this->getFilterPills();

        // Get AI recommendations
        $userLikedIds = [];
        if (Auth::check()) {
            $userLikedIds = LikeRecipe::where('user_id', Auth::id())
                ->pluck('recipe_id')
                ->toArray();
        }

        $hero_count = 5;
        $recommended_count = 12;
        $userId = $request->user() ? $request->user()->user_id : null;
        
        $ai = $this->getAIRecommendationCached(
            $userLikedIds, 
            $hero_count + $recommended_count, 
            $userId
        );

        $aiRecipe = $ai['data'];
        $warningMessage = $ai['warning'];

        // Split AI recommendations into hero and recommended
        $hero = $aiRecipe->take($hero_count)->values();
        $recommended = $aiRecipe->slice($hero_count, $recommended_count)->values();

        // Fallback if AI returns empty
        if ($hero->isEmpty()) {
            $fallback = Recipe::select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
                ->withCount('likes')
                ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
                ->inRandomOrder()
                ->limit($hero_count + $recommended_count)
                ->get();
                
            $hero = $fallback->take($hero_count)->values();
            $recommended = $fallback->slice($hero_count, $recommended_count)->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recipes' => $recipes,
                'hero_recipes' => $hero,
                'recommended_recipes' => $recommended,
                'recipe_filter_options' => $pillOptions,
                'active_filter' => [
                    'type' => $filterType,
                    'id' => $filterId ? (int) $filterId : null,
                ],
            ],
            'warning' => $warningMessage
        ]);
    }

    /**
     * Get single recipe detail
     * 
     * @param Request $request
     * @param Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Recipe $recipe)
    {
        // Load all relationships
        $recipe
            ->load([
                'dietaryPreferences:dietary_preference_id,diet_name', // description dihapus
                'allergies:allergy_id,allergy_name', // description dihapus
                'ingredients:ingredient_id,ingredient_name'
            ])
            ->loadCount('likes');

        $user = $request->user();
        $hasAllergyWarning = false;

        if ($user) {
            $recipe->loadExists([
                'likes as liked_by_me' => fn($q) => 
                    $q->where('user_id', $user->user_id),
            ]);

            // Check for allergy warnings
            $recipeAllergyIds = $recipe->allergies->pluck('allergy_id')->toArray();
            $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();
            $intersect = array_intersect($recipeAllergyIds, $userAllergyIds);

            if (!empty($intersect)) {
                $hasAllergyWarning = true;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $recipe,
            'warning' => $hasAllergyWarning ? 
                'This food contains allergens that you are sensitive to. Please be cautious.' : null
        ]);
    }

    /**
     * Custom search recipes with advanced filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => 'nullable|array',
            'ingredients.*' => 'integer|exists:ingredients,ingredient_id',
            'dietary_preferences' => 'nullable|array',
            'dietary_preferences.*' => 'integer|exists:dietary_preferences,dietary_preference_id',
            'allergies' => 'nullable|array',
            'allergies.*' => 'integer|exists:allergies,allergy_id',
            'calories' => 'nullable|numeric|min:0',
            'protein' => 'nullable|numeric|min:0',
            'fat' => 'nullable|numeric|min:0',
            'sodium' => 'nullable|numeric|min:0',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ], [
            'ingredients.array' => 'Ingredients must be an array',
            'ingredients.*.integer' => 'Each ingredient ID must be an integer',
            'ingredients.*.exists' => 'Selected ingredient does not exist',
            'dietary_preferences.array' => 'Dietary preferences must be an array',
            'dietary_preferences.*.integer' => 'Each dietary preference ID must be an integer',
            'dietary_preferences.*.exists' => 'Selected dietary preference does not exist',
            'allergies.array' => 'Allergies must be an array',
            'allergies.*.integer' => 'Each allergy ID must be an integer',
            'allergies.*.exists' => 'Selected allergy does not exist',
            'calories.numeric' => 'Calories must be a number',
            'calories.min' => 'Calories must be at least 0',
            'protein.numeric' => 'Protein must be a number',
            'protein.min' => 'Protein must be at least 0',
            'fat.numeric' => 'Fat must be a number',
            'fat.min' => 'Fat must be at least 0',
            'sodium.numeric' => 'Sodium must be a number',
            'sodium.min' => 'Sodium must be at least 0',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
            'page.integer' => 'Page must be an integer',
            'page.min' => 'Page must be at least 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Query with dietary_preferences and allergies loaded
        $query = Recipe::query()
            ->select(['recipe_id', 'title', 'slug', 'image', 'cooking_time', 'calories', 'protein', 'fat', 'sodium'])
            ->withCount('likes')
            ->with(['dietaryPreferences:dietary_preference_id,diet_name', 'allergies:allergy_id,allergy_name'])
            ->when($request->user(), function ($q) use ($request) {
                $q->withExists([
                    'likes as liked_by_me' => fn($qq) => $qq->where('user_id', $request->user()->user_id),
                ]);
            });

        // Filter by ingredients (must contain ALL selected ingredients)
        if (!empty($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingredientId) {
                $query->whereHas('ingredients', function ($q) use ($ingredientId) {
                    $q->where('ingredients.ingredient_id', $ingredientId);
                });
            }
        }

        // Filter by dietary preferences (must match ALL selected preferences)
        if (!empty($validated['dietary_preferences'])) {
            foreach ($validated['dietary_preferences'] as $dietId) {
                $query->whereHas('dietaryPreferences', function ($q) use ($dietId) {
                    $q->where('dietary_preferences.dietary_preference_id', $dietId);
                });
            }
        }

        // Filter by allergies (must NOT contain any selected allergies)
        if (!empty($validated['allergies'])) {
            foreach ($validated['allergies'] as $allergyId) {
                $query->whereDoesntHave('allergies', function ($q) use ($allergyId) {
                    $q->where('allergies.allergy_id', $allergyId);
                });
            }
        }

        // Nutrient filtering with tolerance
        $tolerance = [
            'calories' => 50,   // kcal
            'protein'  => 5,    // grams
            'fat'      => 5,    // grams
            'sodium'   => 100,  // mg
        ];

        foreach (['calories', 'protein', 'fat', 'sodium'] as $field) {
            if (!empty($validated[$field])) {
                $value = (float) $validated[$field];
                $delta = $tolerance[$field];

                $query->whereBetween($field, [
                    max(0, $value - $delta),
                    $value + $delta,
                ]);
            }
        }

        $perPage = $validated['per_page'] ?? 16;
        $recipes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $recipes,
            'message' => count($recipes->items()) > 0 ? 
                'Found ' . $recipes->total() . ' recipe(s) matching your criteria' : 
                'No recipes found matching your criteria'
        ]);
    }

    /**
     * Get custom search options (for mobile UI)
     * Returns all available filters
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomSearchOptions()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'allergies' => Allergy::all(['allergy_id', 'allergy_name']), // description dihapus
                'dietary_preferences' => DietaryPreference::all(['dietary_preference_id', 'diet_name']), // description dihapus
                'ingredients' => Ingredient::orderBy('ingredient_name')->get(['ingredient_id', 'ingredient_name']),
                'user_allergies' => $user ? $user->allergies->pluck('allergy_id')->toArray() : [],
                'user_dietary_preferences' => $user ? $user->dietaryPreferences->pluck('dietary_preference_id')->toArray() : [],
            ]
        ]);
    }
}