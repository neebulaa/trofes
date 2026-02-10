<?php

namespace App\Http\Controllers\Api;

use App\Models\Guide;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GuideController extends Controller
{
    /**
     * Get all guides with search and pagination
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ], [
            'search.string' => 'Search must be a string',
            'search.max' => 'Search query too long',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = Guide::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $guides = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $guides
        ]);
    }

    /**
     * Get single guide with navigation (prev/next)
     * 
     * @param Guide $guide
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Guide $guide)
    {
        $publishedAt = $guide->published_at;

        // Get next guide
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

        // Get previous guide
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

        // Get other random guides
        $otherGuides = Guide::where('guide_id', '!=', $guide->guide_id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'guide' => $guide,
                'next_guide' => $nextGuide,
                'prev_guide' => $prevGuide,
                'other_guides' => $otherGuides,
            ]
        ]);
    }

    /**
     * Admin: Get all guides (for management)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
        ], [
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must not exceed 100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $perPage = $request->query('per_page', 15);
        $guides = Guide::latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $guides
        ]);
    }

    /**
     * Admin: Create new guide
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'published_at' => 'nullable|date',
        ], [
            'title.required' => 'Title is required',
            'title.max' => 'Title must not exceed 255 characters',
            'content.required' => 'Content is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, jpg, png, or gif',
            'image.max' => 'Image size must not exceed 2MB',
            'published_at.date' => 'Invalid published date format',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('guides', 'public');
        }

        if (!isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $guide = Guide::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guide created successfully',
            'data' => $guide
        ], 201);
    }

    /**
     * Admin: Update guide
     * 
     * @param Request $request
     * @param Guide $guide
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Guide $guide)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'published_at' => 'nullable|date',
        ], [
            'title.required' => 'Title is required',
            'title.max' => 'Title must not exceed 255 characters',
            'content.required' => 'Content is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, jpg, png, or gif',
            'image.max' => 'Image size must not exceed 2MB',
            'published_at.date' => 'Invalid published date format',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($guide->image) {
                Storage::disk('public')->delete($guide->image);
            }
            $validated['image'] = $request->file('image')->store('guides', 'public');
        }

        $guide->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guide updated successfully',
            'data' => $guide
        ]);
    }

    /**
     * Admin: Delete guide
     * 
     * @param Guide $guide
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Guide $guide)
    {
        // Delete image if exists
        if ($guide->image) {
            Storage::disk('public')->delete($guide->image);
        }

        $guide->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guide deleted successfully'
        ]);
    }
}