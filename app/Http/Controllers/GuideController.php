<?php

namespace App\Http\Controllers;

use App\Http\Resources\GuideResource;
use App\Models\Guide;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuideController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = Guide::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $guides = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        // using guide resource
        return Inertia::render('Guides', [
            'guides' => GuideResource::collection($guides),
        ]);
    }


    public function show(Request $request, Guide $guide){
        return Inertia::render('GuideDetail', [
            'guide' => $guide
        ]);
    }
}
