<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Recipe;
use App\Models\Allergy;
use App\Models\Ingredient;
use App\Models\LikeRecipe;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Illuminate\Cache\RateLimiting\Limit;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecipeController extends Controller
{
    private function randomRows($query, int $limit)
    {
        $count = (clone $query)->count();
        if ($count <= $limit) return $query->limit($limit)->get();

        $offset = random_int(0, max(0, $count - $limit));
        return $query->offset($offset)->limit($limit)->get();
    }

    private function getFilterPillsFromSession(): array
    {
        $sessionKey = 'recipes.filter_pills_v1';

        if (session()->has($sessionKey)) {
            return session()->get($sessionKey);
        }

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

        $ingredientPills =$this->randomRows(
            Ingredient::query()->select(['ingredient_id','ingredient_name']),
            6
        )->map(fn ($i) => [
            'key' => "ingredient:{$i->ingredient_id}",
            'label' => ucfirst($i->ingredient_name),
            'type' => 'ingredient',
            'id' => $i->ingredient_id,
        ]);

        $dietPills = $this->randomRows(
            DietaryPreference::query()->select(['dietary_preference_id','diet_name']),
            6
        )->map(fn ($d) => [
            'key' => "diet:{$d->dietary_preference_id}",
            'label' => $d->diet_name,
            'type' => 'diet',
            'id' => $d->dietary_preference_id,
        ]);

        $allergyPills = $this->randomRows(
            Allergy::query()->select(['allergy_id','allergy_name']),
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

        session()->put($sessionKey, $pillOptions);

        return $pillOptions;
    }

    private function getAIRecommendationCached(array $likedRecipeIds, int $limit, ?int $userId)
    {
        if (empty($likedRecipeIds)) {
            return [
                'data' => Recipe::inRandomOrder()->limit($limit)->get(),
                'warning' => null
            ];
        }
        $user = Auth::user();
        // Ambil ID alergi dan diet untuk dijadikan bagian dari Key Cache
        $allergyHash = $user ? md5(json_encode($user->allergies->pluck('allergy_id')->sort()->toArray())): 'no-allergy';
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
                    throw new \Exception("AI API Down"); // Agar cache gak diisi random dan bakal coba lagi di refresh berikutnya
                    // return collect(); // no cache of error response body
                }

                if($response->successful()){
                    $recommendedIds = $response->json('recommended_ids') ?? [];
                    $filterStatus = 'none'; 
                    // dd($recommendedIds);
                    if (empty($recommendedIds)) {
                        return ['data' => Recipe::inRandomOrder()->limit($limit)->get(), 'warning' => null];
                    }

                    // 3. Mulai Hard Filtering (Logika Kode Awalmu)
                    $baseQuery = Recipe::query()->whereIn('recipe_id', $recommendedIds);

                    if ($user) {
                        // Filter Alergi
                        $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();
                        if (!empty($userAllergyIds)) {
                            $baseQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                                $q->whereIn('allergies.allergy_id', $userAllergyIds);
                            });
                        }
                    }

                    // Filter Diet
                    $warningMessage = null;
                    $userDietIds = $user ? $user->dietaryPreferences->pluck('dietary_preference_id')->toArray(): [];
                    if (!empty($userDietIds)) {
                        $perfectQuery = (clone $baseQuery);
                        foreach($userDietIds as $dietId){
                            $perfectQuery->whereHas('dietaryPreferences', fn($q) => $q->where('dietary_preferences.dietary_preference_id', $dietId));
                        }
                        if ($perfectQuery->count() > 0) {
                            // dd("Perfect bre $perfectQuery");
                            $query = $perfectQuery;
                            $filterStatus = 'Perfect';
                        } else {
                            $partialQuery = (clone $baseQuery)->whereHas('dietaryPreferences', function ($q) use ($userDietIds) {
                                $q->whereIn('dietary_preferences.dietary_preference_id', $userDietIds);
                            });

                            if ($partialQuery->count() > 0) {
                                $query = $partialQuery;
                                $warningMessage = "We couldn't find recipes matching ALL your diets, so we're showing some that match at least one.";
                                $filterStatus = 'Partial';
                            } else {
                                $query = $baseQuery;
                                $filterStatus = 'Random';
                            }
                        }
                    }else{
                        $query = $baseQuery;
                        $filterStatus = 'Gak set diet';
                        // dd("else bre $query");
                    }
                        
                    // $afterFilterCount = (clone $query)->count();
                    // dd("DEBUG FILTER $filterStatus: Dari " . count($recommendedIds) . " resep AI, hanya $afterFilterCount yang lolos filter Diet/Alergi.");

                    // Ambil hasil sesuai urutan rekomendasi AI
                    $idsString = implode(',', $recommendedIds);
                    $recommended = $query->orderByRaw("FIELD(recipe_id, $idsString)")
                        ->take($limit)
                        ->get();

                    // 4. Fallback jika setelah difilter hasilnya kurang dari limit
                    if ($recommended->count() < $limit) {
                        $needed = $limit - $recommended->count();
                        // Buat query dasar untuk fallback yang tetap aman
                        $fallbackQuery = Recipe::whereNotIn('recipe_id', $recommended->pluck('recipe_id'));
                        if ($user) {
                            // Tetap buang alergi di hasil random
                            if (!empty($userAllergyIds)) {
                                $fallbackQuery->whereDoesntHave('allergies', function ($q) use ($userAllergyIds) {
                                    $q->whereIn('allergies.allergy_id', $userAllergyIds);
                                });
                            }
                            // Tetap pastikan sesuai diet di hasil random
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
                }
            } catch (\Throwable $e) {
                Log::warning('AI recommend exception', ['msg' => $e->getMessage()]);
                return [
                    'data' => Recipe::inRandomOrder()->limit($limit)->get(),
                    'warning' => null
                ];
            }
            return [
                'data' => Recipe::inRandomOrder()->limit($limit)->get(),
                'warning' => null
            ];
        });
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 16);

        $filterType = $request->query('filter_type'); // popular/{ingredient}/{diet}/{no_allergy}
        $filterId = $request->query('filter_id'); // integer for ingredient/diet/no_allergy

        $query = Recipe::query()
            ->select(['recipe_id','title','slug','image','cooking_time'])
            ->withCount('likes')
            ->when($request->user(), function ($q) use ($request) {
                $q->withExists([
                    'likes as liked_by_me' => fn ($qq) => $qq->where('user_id', $request->user()->user_id),
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

        // apply pill filters
        if ($filterType === 'popular') {
            $query->orderByDesc('likes_count');
        } elseif ($filterType === 'ingredient' && $filterId) {
            $query->whereHas('ingredients', fn ($q) => $q->where('ingredients.ingredient_id', (int) $filterId));
        } elseif ($filterType === 'diet' && $filterId) {
            $query->whereHas('dietaryPreferences', fn ($q) => $q->where('dietary_preferences.dietary_preference_id', (int) $filterId));
        } elseif ($filterType === 'no_allergy' && $filterId) {
            // "no X" means exclude recipes that contain that allergy
            $query->whereDoesntHave('allergies', fn ($q) => $q->where('allergies.allergy_id', (int) $filterId));
        }

        $maxLikes = Recipe::query()
            ->withCount('likes')
            ->orderByDesc('likes_count')
            ->value('likes_count') ?? 0;

        $recipes = $query
            ->paginate($perPage)
            ->appends($request->only(['search', 'per_page', 'filter_type', 'filter_id']));

        $recipes->getCollection()->transform(function ($recipe) use ($maxLikes) {
            $recipe->is_favorite = ($maxLikes > 0) && ((int) $recipe->likes_count === (int) $maxLikes);
            return $recipe;
        });

        if ($request->boolean('refresh_pills')) {
            session()->forget('recipes.filter_pills_v1');
        }

        $pillOptions = $this->getFilterPillsFromSession();
        $userLikedIds = [];
        if(Auth::check()){
            $userLikedIds = LikeRecipe::where('user_id', Auth::id())->pluck('recipe_id')->toArray();
        }

        $hero_count = 5;
        $recommended_count = 12;
        $userId = $request->user()->user_id;
        $ai = $this->getAIRecommendationCached($userLikedIds, $hero_count + $recommended_count, $userId);
        
        $aiRecipe = $ai['data'];
        $warningMessage = $ai['warning'];

        $hero = $aiRecipe->take($hero_count)->values();
        $recommended = $aiRecipe->slice($hero_count, $recommended_count)->values();

        // fallback if AI empty
        if ($hero->isEmpty()) {
            $fallback = Recipe::inRandomOrder()->limit($hero_count + $recommended_count)->get();
            $hero = $fallback->take($hero_count)->values();
            $recommended = $fallback->slice($hero_count, $recommended_count)->values();
        }

        return Inertia::render('Recipes', [
            'recipes' => $recipes,
            'hero_recipes' => $hero,
            'recommended_recipes' => $recommended,
            'recipe_filter_options' => $pillOptions,
            'active_filter' => [
                'type' => $filterType,
                'id' => $filterId ? (int) $filterId : null,
            ],
        ])->with('flash', $warningMessage ? [
            'type' => 'warning',
            'message' => $warningMessage
        ] : null);
    }

    public function show(Request $request, Recipe $recipe){
        
        $recipe
            ->load(['dietaryPreferences', 'allergies'])
            ->loadCount('likes');
        
        $user = $request->user();
        $hasAllergyWarning = false;

        if ($user) {
            $recipe->loadExists([
                'likes as liked_by_me' => fn ($q) =>
                    $q->where('user_id', $request->user()->user_id),
            ]);

            // LOGIKA CEK ALERGI:
            // Ambil ID alergi yang ada di resep ini
            $recipeAllergyIds = $recipe->allergies->pluck('allergy_id')->toArray();
            // Ambil ID alergi yang dimiliki user
            $userAllergyIds = $user->allergies->pluck('allergy_id')->toArray();

            // Cek apakah ada ID yang beririsan (match)
            $intersect = array_intersect($recipeAllergyIds, $userAllergyIds);
            
            if (!empty($intersect)) {
                $hasAllergyWarning = true;
            }
        }

        return Inertia::render('RecipeDetail', [
            'recipe' => $recipe, // loadCount sudah dilakukan di awal
            'user' => $user,
        ])->with('flash', $hasAllergyWarning ? [
            'type' => 'error',
            'message' => "This food contains allergens that you are sensitive to. Please be cautious when preparing or consuming this dish."
        ] : null);
    }

    public function customSearchRecipes(Request $request){
        return inertia('CustomSearchRecipes', [
            'allergies' => Allergy::all(),
            'dietary_preferences' => DietaryPreference::all(),
            'user_allergies' => Auth::user()->allergies->pluck('allergy_id')->toArray(),
            'user_dietary_preferences' => Auth::user()->dietaryPreferences->pluck('dietary_preference_id')->toArray(),
            'user' => Auth::user(), 
            'ingredients' => Ingredient::all()
        ]);
    }

    public function performCustomSearchRecipes(Request $request)
    {
        $validated = $request->validate([
            'ingredients' => 'array',
            'ingredients.*' => 'integer|exists:ingredients,ingredient_id',
            'dietary_preferences' => 'array',
            'dietary_preferences.*' => 'integer|exists:dietary_preferences,dietary_preference_id',
            'allergies' => 'array',
            'allergies.*' => 'integer|exists:allergies,allergy_id',
            'calories' => 'nullable|numeric|min:0',
            'protein'  => 'nullable|numeric|min:0',
            'fat'      => 'nullable|numeric|min:0',
            'sodium'   => 'nullable|numeric|min:0',
        ]);

        $query = Recipe::query()->withCount('likes');

        // recipe contains all selected ingredients huh
        if (!empty($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingredientId) {
                $query->whereHas('ingredients', function ($q) use ($ingredientId) {
                    $q->where('ingredients.ingredient_id', $ingredientId);
                });
            }
        }

        // recipe matches all selected dietary preferences
        if (!empty($validated['dietary_preferences'])) {
            foreach ($validated['dietary_preferences'] as $dietId) {
                $query->whereHas('dietaryPreferences', function ($q) use ($dietId) {
                    $q->where(
                        'dietary_preferences.dietary_preference_id',
                        $dietId
                    );
                });
            }
        }

        // recipe must not contains all selected allergies
        if (!empty($validated['allergies'])) {
            foreach ($validated['allergies'] as $allergyId) {
                $query->whereDoesntHave('allergies', function ($q) use ($allergyId) {
                    $q->where('allergies.allergy_id', $allergyId);
                });
            }
        }

        // ± tolerance makes search usable
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

        // sorting but optional nanti lah diskusi
        // $query->orderByDesc('likes_count');

        $recipes = $query->paginate(16)->withQueryString();
        return Inertia::render('Recipes', [
            'recipes' => $recipes,
            'hero_recipes' => Recipe::inRandomOrder()->limit(5)->get(),
            'recommended_recipes' => Recipe::inRandomOrder()->limit(4)->get(),
            'recipe_filter_options' => $this->getFilterPillsFromSession(),
            'active_filter' => null,
        ]);
    }

}
