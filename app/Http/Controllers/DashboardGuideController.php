<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Guide;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DashboardGuideController extends Controller
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

        return Inertia::render('Dashboard/Guides/Index', [
            'guides' => $guides
        ]);
    }

    public function create(){
        return Inertia::render('Dashboard/Guides/Create');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('guides', 'public');
        }

        $validated['content'] = Purifier::clean($validated['content']);

        $guide = Guide::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'content' => $validated['content'], // store HTML
            'image' => $imagePath,
            'admin_id' => auth()->id(),
            'published_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => Auth::user()->user_id,
            'action' => 'guide.added',
            'meta' => json_encode([
                'guide' => $guide->title,
            ]),
        ]);

        return redirect('/dashboard/guides')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Guide published successfully.',
            ]);
    }

    public function show(Guide $guide){
        return Inertia::render('Dashboard/Guides/Show', [
            'guide' => $guide,
        ]);
    }

    public function edit(Guide $guide){
        return Inertia::render('Dashboard/Guides/Edit', [
            'guide' => $guide,
        ]);
    }

    public function update(Request $request, Guide $guide){
        $purifierPath = storage_path('app/purifier/HTML');
        if (!File::exists($purifierPath)) {
            File::makeDirectory($purifierPath, 0755, true);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_removed' => ['nullable', 'in:0,1'],
            'image' => [
                'nullable',
                'required_if:image_removed,1',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'image.required_if' => 'The image field is required when removing the existing image.',
        ]);
        
        if ($request->hasFile('image')) {
            if ($guide->image) {
                Storage::disk('public')->delete($guide->image);
            }
            $imagePath = $request->file('image')->store('guides', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['content'] = Purifier::clean($validated['content']);

        $guide->update([
            'title' => $validated['title'],
            'content' => $validated['content'], // store HTML
            'image' => $validated['image'] ?? $guide->image,
        ]);

        ActivityLog::create([
            'user_id' => Auth::user()->user_id,
            'action' => 'guide.updated',
            'meta' => json_encode([
                'guide' => $guide->title,
            ]),
        ]);

        return redirect('/dashboard/guides')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Guide updated successfully.',
            ]);
    }

    public function destroy(Guide $guide){
        if ($guide->image) {
            Storage::disk('public')->delete($guide->image);
        }

        ActivityLog::create([
            'user_id' => Auth::user()->user_id,
            'action' => 'guide.deleted',
            'meta' => json_encode([
                'guide' => $guide->title,
            ]),
        ]);

    $guide->delete();

        return redirect('/dashboard/guides')
            ->with('flash', [
                'type' => 'success',
                'message' => 'Guide deleted successfully.',
            ]);
    }
}
