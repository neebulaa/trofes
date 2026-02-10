<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuideController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = Guide::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $guides = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        // using guide resource
        return Inertia::render('Guides', [
            'guides' => $guides,
        ]);
    }


    public function show(Request $request, Guide $guide){
        $publishedAt = $guide->published_at;

        $nextGuide = Guide::query()
            ->where(function ($q) use ($publishedAt, $guide) {
                $q->where('published_at', '>', $publishedAt)
                ->orWhere(function ($q2) use ($publishedAt, $guide) {
                    $q2->where('published_at', '=', $publishedAt)
                        ->where('guide_id', '>', $guide->guide_id);
                });
            })
            ->orderBy('published_at', 'asc')
            ->orderBy('guide_id', 'asc')
            ->first();

        $prevGuide = Guide::query()
            ->where(function ($q) use ($publishedAt, $guide) {
                $q->where('published_at', '<', $publishedAt)
                ->orWhere(function ($q2) use ($publishedAt, $guide) {
                    $q2->where('published_at', '=', $publishedAt)
                        ->where('guide_id', '<', $guide->guide_id);
                });
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('guide_id', 'desc')
            ->first();

        return Inertia::render('GuideDetail', [
            'guide' => $guide,
            'next_guide' => $nextGuide,
            'prev_guide' => $prevGuide,
            'other_guides' => Guide::where('guide_id', '!=', $guide->guide_id)
                ->inRandomOrder()
                ->limit(5)
                ->get(),
        ]);
    }
}
