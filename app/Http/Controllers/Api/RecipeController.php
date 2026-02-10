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
     * Get AI Recommendations with caching
     */
    private function getAIRecommendationCached(array $likedRecipeIds, int $limit, ?int $userId)
    {
        if (empty($likedRecipeIds)) {
            return [
                'data' => Recipe::inRandomOrder()->limit($limit)->get(),
                'warning' => null
            ];
        }

        $user = Auth::user();
        $allergyHash = $user ? md5(json_encode($user->allergies->pluck('allergy_id')->sort()->toArray())) : 'no-allergy';
        $dietHash = $user ? md5(json_encode($user->dietaryPreferences->pluck('dietary_preference_id')->sort()->toArray())) : 'no-diet';

        $likeHash = md5(json_encode($likedRecipeIds));
        $cacheKey = "ai_rec_v6:u={$userId}:l={$likeHash}:a={$allergyHash}:d={$dietHash}:lim={$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($likedRecipeIds, $limit, $user) {
            try {
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
                    return ['data' => Recipe::inRandomOrder()->limit($limit)->get(), 'warning' => null];
                }

                $baseQuery = Recipe::query()->whereIn('recipe_id', $recommendedIds);

                if ($user) {
                    $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();
                    if (!empty($userAllergyIds)) {
                        $baseQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                            $q->whereIn('allergies.allergy_id', $userAllergyIds);
                        });
                    }
                }

                $warningMessage = null;
                $userDietIds = $user ? $user->dietaryPreferences->pluck('dietary_preference_id')->toArray() : [];

                if (!empty($userDietIds)) {
                    $perfectQuery = (clone $baseQuery);
                    foreach ($userDietIds as $dietId) {
                        $perfectQuery->whereHas('dietaryPreferences', fn($q) => $q->where('dietary_preferences.dietary_preference_id', $dietId));
                    }

                    if ($perfectQuery->count() > 0) {
                        $query = $perfectQuery;
                    } else {
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

                $idsString = implode(',', $recommendedIds);
                $recommended = $query->orderByRaw("FIELD(recipe_id, $idsString)")
                    ->take($limit)
                    ->get();

                if ($recommended->count() < $limit) {
                    $needed = $limit - $recommended->count();
                    $fallbackQuery = Recipe::whereNotIn('recipe_id', $recommended->pluck('recipe_id'));

                    if ($user) {
                        if (!empty($userAllergyIds)) {
                            $fallbackQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                                $q->whereIn('allergies.allergy_id', $userAllergyIds);
                            });
                        }
                        if (!empty($userDietIds)) {
                            foreach ($userDietIds as $dietId) {
                                $fallbackQuery->whereHas('dietaryPreferences', fn($q) => $q->where('dietary_preferences.dietary_preference_id', $dietId));
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
                return [
                    'data' => Recipe::inRandomOrder()->limit($limit)->get(),
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
            'filter_type' => 'nullable|string|in:popular,ingredient,diet,no_allergy',
            'filter_id' => 'nullable|integer|min:1',
        ], [
            'search.string' => 'Search must be a string',
            'search.max' => 'Search query too long',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
            'filter_type.in' => 'Invalid filter type',
            'filter_id.integer' => 'Filter ID must be an integer',
            'filter_id.min' => 'Filter ID must be at least 1',
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

        $query = Recipe::query()
            ->select(['recipe_id', 'title', 'slug', 'image', 'cooking_time'])
            ->withCount('likes')
            ->when($request->user(), function ($q) use ($request) {
                $q->withExists([
                    'likes as liked_by_me' => fn($qq) => $qq->where('user_id', $request->user()->user_id),
                ]);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('measured_ingredients', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($filterType === 'popular') {
            $query->orderByDesc('likes_count');
        } elseif ($filterType === 'ingredient' && $filterId) {
            $query->whereHas('ingredients', fn($q) => $q->where('ingredients.ingredient_id', (int) $filterId));
        } elseif ($filterType === 'diet' && $filterId) {
            $query->whereHas('dietaryPreferences', fn($q) => $q->where('dietary_preferences.dietary_preference_id', (int) $filterId));
        } elseif ($filterType === 'no_allergy' && $filterId) {
            $query->whereDoesntHave('allergies', fn($q) => $q->where('allergies.allergy_id', (int) $filterId));
        }

        $maxLikes = Recipe::withCount('likes')->orderByDesc('likes_count')->value('likes_count') ?? 0;

        $recipes = $query->paginate($perPage);

        $recipes->getCollection()->transform(function ($recipe) use ($maxLikes) {
            $recipe->is_favorite = ($maxLikes > 0) && ((int) $recipe->likes_count === (int) $maxLikes);
            return $recipe;
        });

        // AI Recommendations
        $userLikedIds = [];
        if (Auth::check()) {
            $userLikedIds = LikeRecipe::where('user_id', Auth::id())->pluck('recipe_id')->toArray();
        }

        $hero_count = 5;
        $recommended_count = 12;
        $userId = $request->user() ? $request->user()->user_id : null;
        $ai = $this->getAIRecommendationCached($userLikedIds, $hero_count + $recommended_count, $userId);

        $aiRecipe = $ai['data'];
        $warningMessage = $ai['warning'];

        $hero = $aiRecipe->take($hero_count)->values();
        $recommended = $aiRecipe->slice($hero_count, $recommended_count)->values();

        if ($hero->isEmpty()) {
            $fallback = Recipe::inRandomOrder()->limit($hero_count + $recommended_count)->get();
            $hero = $fallback->take($hero_count)->values();
            $recommended = $fallback->slice($hero_count, $recommended_count)->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recipes' => $recipes,
                'hero_recipes' => $hero,
                'recommended_recipes' => $recommended,
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
        $recipe
            ->load(['dietaryPreferences', 'allergies', 'ingredients'])
            ->loadCount('likes');

        $user = $request->user();
        $hasAllergyWarning = false;

        if ($user) {
            $recipe->loadExists([
                'likes as liked_by_me' => fn($q) => $q->where('user_id', $user->user_id),
            ]);

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
            'warning' => $hasAllergyWarning ? 'This food contains allergens that you are sensitive to. Please be cautious.' : null
        ]);
    }

    /**
     * Custom search recipes
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $query = Recipe::query()->withCount('likes');

        if (!empty($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingredientId) {
                $query->whereHas('ingredients', function ($q) use ($ingredientId) {
                    $q->where('ingredients.ingredient_id', $ingredientId);
                });
            }
        }

        if (!empty($validated['dietary_preferences'])) {
            foreach ($validated['dietary_preferences'] as $dietId) {
                $query->whereHas('dietaryPreferences', function ($q) use ($dietId) {
                    $q->where('dietary_preferences.dietary_preference_id', $dietId);
                });
            }
        }

        if (!empty($validated['allergies'])) {
            foreach ($validated['allergies'] as $allergyId) {
                $query->whereDoesntHave('allergies', function ($q) use ($allergyId) {
                    $q->where('allergies.allergy_id', $allergyId);
                });
            }
        }

        $tolerance = [
            'calories' => 50,
            'protein' => 5,
            'fat' => 5,
            'sodium' => 100,
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
            'data' => $recipes
        ]);
    }
}