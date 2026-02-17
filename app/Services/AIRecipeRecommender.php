<?php
namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIRecipeRecommender
{
    public function byLikeRecommendCached(array $likedRecipeIds, int $limit, ?int $userId = null): array
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
                    // dd([
                    //     'status' => $response->status(),
                    //     'body' => $response->body(), 
                    //     'json' => $response->json()
                    // ]);
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
                // dd($recommendedIds);
                // dd('random');
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
}