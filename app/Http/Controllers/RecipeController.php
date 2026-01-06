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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecipeController extends Controller
{
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

        $ingredientPills = Ingredient::inRandomOrder()->limit(6)->get()->map(fn ($i) => [
            'key' => "ingredient:{$i->ingredient_id}",
            'label' => ucfirst($i->ingredient_name),
            'type' => 'ingredient',
            'id' => $i->ingredient_id,
        ]);

        $dietPills = DietaryPreference::inRandomOrder()->limit(6)->get()->map(fn ($d) => [
            'key' => "diet:{$d->dietary_preference_id}",
            'label' => $d->diet_name,
            'type' => 'diet',
            'id' => $d->dietary_preference_id,
        ]);

        $allergyPills = Allergy::inRandomOrder()->limit(6)->get()->map(fn ($a) => [
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

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 16);

        $filterType = $request->query('filter_type'); // popular/{ingredient}/{diet}/{no_allergy}
        $filterId = $request->query('filter_id'); // integer for ingredient/diet/no_allergy

        $query = Recipe::query()->withCount('likes');

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

        return Inertia::render('Recipes', [
            'recipes' => $recipes,
            'hero_recipes' => Recipe::inRandomOrder()->limit(5)->get(),
            'recommended_recipes' => Recipe::inRandomOrder()->limit(4)->get(),
            'recipe_filter_options' => $pillOptions,
            'active_filter' => [
                'type' => $filterType,
                'id' => $filterId ? (int) $filterId : null,
            ],
        ]);
    }

    public function show(Recipe $recipe){
        return Inertia::render('RecipeDetail', [
            'recipe' => $recipe->loadCount('likes'),
            'user' => Auth::user(),
        ]);
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
}
