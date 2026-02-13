<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Recipe;
use App\Models\Allergy;
use App\Models\Ingredient;
use App\Models\LikeRecipe;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use Illuminate\Support\Facades\Auth;
use App\Services\AIRecipeRecommender;

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

    private function applyCustomSearchFilters($query, array $filters)
    {
        if (!empty($filters['ingredients'])) {
            $ingredientIds = $filters['ingredients'];
            $query->withCount(['ingredients as matched_ingredients_count' => function($q) use ($ingredientIds) {
                $q->whereIn('ingredients.ingredient_id', $ingredientIds);
            }]);
            
            $query->orderByDesc('matched_ingredients_count');
        }
        
        // Filter Dietary & Allergy tetap sama (karena ini wajib/hard constraint)
        if (!empty($filters['dietary_preferences'])) {
            foreach ($filters['dietary_preferences'] as $dietId) {
                $query->whereHas('dietaryPreferences', fn($q) => $q->where('dietary_preferences.dietary_preference_id', (int) $dietId));
            }
        }

        if (!empty($filters['allergies'])) {
            foreach ($filters['allergies'] as $allergyId) {
                $query->whereDoesntHave('allergies', fn($q) => $q->where('allergies.allergy_id', (int) $allergyId));
            }
        }

        // Ambil nilai input nutrisi
        $cal = (float) ($filters['calories'] ?? 0);
        $pro = (float) ($filters['protein'] ?? 0);
        $fat = (float) ($filters['fat'] ?? 0);
        $carb = (float) ($filters['carbohydrate'] ?? 0);

        // Di dalam applyCustomSearchFilters, setelah bagian hitung score_distance
        if ($cal > 0 || $pro > 0 || $fat > 0 || $carb > 0) {
            $query->addSelect(\DB::raw("(
                POWER(calories - $cal, 2) + 
                POWER(protein - $pro, 2) * 5 + 
                POWER(fat - $fat, 2) * 5 + 
                POWER(carbohydrate - $carb, 2) * 2
            ) AS score_distance"));

            // Tambahkan kalkulasi persentase (Sederhana: makin jauh dari 0, makin kecil %)
            // Kita asumsikan jarak 10.000 adalah 0% match sebagai batas aman
            $query->addSelect(\DB::raw("
                ROUND(GREATEST(0, 100 - (SQRT(
                    POWER(calories - $cal, 2) * 0.4 + 
                    POWER(protein - $pro, 2) * 1.5 + 
                    POWER(fat - $fat, 2) * 1.5 + 
                    POWER(carbohydrate - $carb, 2) * 0.8
                ) / 10)), 1) AS match_percentage
            "));

            $query->orderBy('score_distance', 'asc');
        } else {
            // Default jika tidak ada input nutrisi
            $query->addSelect(\DB::raw("100 AS match_percentage"));
        }

        return $query;
    }

    public function index(Request $request, AIRecipeRecommender $ai)
    {

        $mode = $request->query('mode'); // 'custom' or null
        $isCustomMode = ($mode === 'custom');

        if (!$isCustomMode) {
            session()->forget('recipes.custom_search_filters_v1');
        }

        $customFilters = $isCustomMode
            ? session()->get('recipes.custom_search_filters_v1')
            : null;

        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 16);

        $filterType = $request->query('filter_type'); // popular/{ingredient}/{diet}/{no_allergy}
        $filterId = $request->query('filter_id'); // integer for ingredient/diet/no_allergy

        $query = Recipe::query()
        // PENTING: Gunakan tabel.* agar selectRaw nantinya tidak error
            ->select(['recipes.recipe_id','recipes.title','recipes.slug','recipes.image','recipes.cooking_time'])
            ->withCount('likes')
            ->when($request->user(), function ($q) use ($request) {
                $q->withExists([
                    'likes as liked_by_me' => fn ($qq) => $qq->where('user_id', $request->user()->user_id),
                ]);
            });

        if ($isCustomMode && is_array($customFilters)) {
            $this->applyCustomSearchFilters($query, $customFilters);
        }

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
            ->appends($request->only(['search', 'per_page', 'filter_type', 'filter_id', 'mode']));

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
        $recommended_count_to_share = 8;
        $userId = $request->user()->user_id;
        $ai = $ai->byLikeRecommendCached($userLikedIds, $hero_count + $recommended_count, $userId);
        
        $aiRecipe = $ai['data'];
        $warningMessage = $ai['warning'];

        $hero = $aiRecipe->take($hero_count)->values();
        $recommended = $aiRecipe->slice($hero_count, $recommended_count)->values()->shuffle()->take($recommended_count_to_share)->values();

        // fallback if AI empty
        if ($hero->isEmpty()) {
            $fallback = Recipe::inRandomOrder()->limit($hero_count + $recommended_count)->get();
            $hero = $fallback->take($hero_count)->values();
            $recommended = $fallback->slice($hero_count, $recommended_count)->values()->shuffle()->take($recommended_count_to_share)->values();
        }

        // for custom search recipes showing
        $ingredientOptions = Ingredient::query()
            ->select(['ingredient_id', 'ingredient_name'])
            ->orderBy('ingredient_name')
            ->get()
            ->map(fn ($i) => [
                'value' => (int) $i->ingredient_id,
                'label' => ucfirst($i->ingredient_name),
            ]);

        $dietOptions = DietaryPreference::query()
            ->select(['dietary_preference_id', 'diet_name'])
            ->orderBy('diet_name')
            ->get()
            ->map(fn ($d) => [
                'value' => (int) $d->dietary_preference_id,
                'label' => $d->diet_name,
            ]);

        $allergyOptions = Allergy::query()
            ->select(['allergy_id', 'allergy_name'])
            ->orderBy('allergy_name')
            ->get()
            ->map(fn ($a) => [
                'value' => (int) $a->allergy_id,
                'label' => $a->allergy_name,
            ]);

        return Inertia::render('Recipes', [
            'recipes' => $recipes,
            'hero_recipes' => $hero,
            'recommended_recipes' => $recommended,
            'recipe_filter_options' => $pillOptions,
            'active_filter' => [
                'type' => $filterType,
                'id' => $filterId ? (int) $filterId : null,
            ],
            'is_custom_mode' => $isCustomMode,
            'custom_filters' => $isCustomMode ? $customFilters : null,
            'ingredient_options' => $ingredientOptions,
            'diet_options' => $dietOptions,
            'allergy_options' => $allergyOptions
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
            'carbohydrate'   => 'nullable|numeric|min:0',
        ]);

        session()->put('recipes.custom_search_filters_v1', $validated);

        return redirect()->route('recipes.index', [
            'mode' => 'custom',
        ]);
    }

}
