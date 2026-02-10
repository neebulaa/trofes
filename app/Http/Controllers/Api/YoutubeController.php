<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class YoutubeController extends Controller
{
    /**
     * Search YouTube videos
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:255',
        ], [
            'q.string' => 'Search query must be a string',
            'q.max' => 'Search query too long',
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
                'data' => [
                    'videoId' => null,
                    'embedUrl' => null
                ]
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

        $result = Cache::remember($cacheKey, now()->addHours(24), function () use ($q, $key) {
            try {
                $res = Http::timeout(10)->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $q,
                    'type' => 'video',
                    'maxResults' => 5,
                    'order' => 'relevance',
                    'videoEmbeddable' => 'true',
                    'videoSyndicated' => 'true',
                    'safeSearch' => 'strict',
                    'relevanceLanguage' => 'en',
                    'key' => $key,
                ]);

                if (!$res->ok()) {
                    return [
                        'success' => false,
                        'error' => 'YouTube API request failed',
                        'status' => $res->status()
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
                    'error' => 'YouTube search failed',
                    'message' => $e->getMessage()
                ];
            }
        });

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}