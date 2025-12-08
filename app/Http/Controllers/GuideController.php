<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\GuideResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GuideController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Guide::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $guides = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        return GuideResource::collection($guides)
            ->additional([
                "message" => "Get guides success"
            ])
            ->response()
            ->setStatusCode(200);
    }

    public function show(Request $request, Guide $guide){
        return response([
            "message" => "Get guide success",
            "guide" => $guide
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            "title" => "required|min:5",
            "content" => "required",
            "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048"
        ], [
            "image.image" => "File must be an image",
            "image.mimes" => "Image must be jpg, jpeg, or png",
            "image.max" => "Image must be under 2MB",
        ]);

        if ($validator->fails()) {
            return response([
                "message" => "Invalid input",
                "errors" => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // generate slug
        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $counter = 1;

        while (Guide::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $data['slug'] = $slug;

        // handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('guides', 'public');
            $data['image'] = $imagePath;
        }

        $data['published_at'] = now();
        $data['admin_id'] = auth()->user()->user_id;

        $guide = Guide::create($data);
        
        return response([
            "message" => "Create guide success",
            "guide" => new GuideResource($guide)
        ]);
    }

    public function update(Request $request, Guide $guide)
    {
        $validator = Validator::make($request->all(), [
            "title" => "sometimes|required|min:5",
            "content" => "sometimes|required",
            "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
        ], [
            "image.image" => "File must be an image",
            "image.mimes" => "Image must be jpg, jpeg, or png",
            "image.max" => "Image must be under 2MB",
        ]);

        if ($validator->fails()) {
            return response([
                "message" => "Invalid input",
                "errors" => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // regenerate slug
        if (isset($data['title']) && $data['title'] !== $guide->title) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $counter = 1;

            while (Guide::where('slug', $slug)->where('guide_id', '<>', $guide->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $data['slug'] = $slug;
        }

        // update image
        if ($request->hasFile("image")) {
            if ($guide->image && Storage::disk("public")->exists($guide->image)) {
                Storage::disk("public")->delete($guide->image);
            }

            $data["image"] = $request->file("image")->store("guides", "public");
        }

        $guide->update($data);

        return response([
            "message" => "Update guide success",
            "guide" => new GuideResource($guide),
        ], 200);
    }

    
    public function destroy(Request $request, Guide $guide)
    {
        // delete image
        if ($guide->image && Storage::disk('public')->exists($guide->image)) {
            Storage::disk('public')->delete($guide->image);
        }

        $guide->delete();

        return response([
            "message" => "Delete guide success",
        ], 200);
    }
}
