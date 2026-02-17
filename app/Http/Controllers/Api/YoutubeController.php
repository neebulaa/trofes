<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class YoutubeController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $q = trim($request->query('q', ''));
        
        if ($q === '') {
            return response()->json([
                'success' => true,
                'videoId' => null,
                'embedUrl' => null
            ]);
        }

        $key = config('services.youtube.key');
        
        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'YouTube API key not configured'
            ], 500);
        }

        $cacheKey = 'yt_search_' . md5(mb_strtolower($q));

        // Memastikan yang disimpan di cache HANYA array data, bukan objek response
        $result = Cache::remember($cacheKey, now()->addHours(24), function () use ($q, $key) {
            try {
                $res = Http::timeout(10)->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $q,
                    'type' => 'video',
                    'maxResults' => 1, // Kita hanya butuh 1 untuk embed utama
                    'videoEmbeddable' => 'true',
                    'key' => $key,
                ]);

                if (!$res->ok()) {
                    return [
                        'success' => false,
                        'message' => 'YouTube API request failed'
                    ];
                }

                $items = $res->json('items') ?? [];
                $videoId = $items[0]['id']['videoId'] ?? null;

                return [
                    'success' => true,
                    'videoId' => $videoId,
                    'embedUrl' => $videoId ? "https://www.youtube.com/embed/{$videoId}" : null,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'YouTube search failed'
                ];
            }
        });

        // Cek jika cache menyimpan data error, kita hapus cache agar bisa mencoba lagi nanti
        if (isset($result['success']) && !$result['success']) {
            Cache::forget($cacheKey);
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}