<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YoutubeController extends Controller
{
    public function search(Request $request)
    {
        
        $q = trim($request->query('q', ''));
        if ($q === '') {
            return response()->json(['videoId' => null, 'embedUrl' => null], 200);
        }

        $key = config('services.youtube.key');
        if (!$key) {
            return response()->json(['error' => 'YouTube API key missing'], 500);
        }

        // Cache to reduce quota usage (search.list costs quota) :contentReference[oaicite:4]{index=4}
        $cacheKey = 'yt_search_' . md5(mb_strtolower($q));

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($q, $key) {
            $res = Http::get('https://www.googleapis.com/youtube/v3/search', [
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
                return response()->json([
                    'error' => 'YouTube request failed',
                    'body' => $res->json(),
                    'status' => $res->status(),
                ], 502);
            }

            $items = $res->json('items') ?? [];
            $videoId = $items[0]['id']['videoId'] ?? null;

            return response()->json([
                'videoId' => $videoId,
                // Embed format :contentReference[oaicite:5]{index=5}
                'embedUrl' => $videoId ? "https://www.youtube.com/embed/{$videoId}" : null,
            ]);
        });
    }
}
