<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use Illuminate\Support\Facades\Storage;

class DashboardDietaryPreferenceController extends Controller
{
    private function generateDietCode($dietName)
    {
        $code = strtolower($dietName);
        $code = preg_replace('/\s+/', '_', $code);
        $code = trim($code, '_');
        
        if (empty($code)) {
            $code = 'dietary_preference_' . Str::random(6);
        }
        
        return $code;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = DietaryPreference::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('diet_code', 'like', "%{$search}%")
                ->orWhere('diet_name', 'like', "%{$search}%");
            });
        }

        $dietaryPreferences = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        return Inertia::render('Dashboard/DietaryPreferences/Index', [
            'dietary_preferences' => $dietaryPreferences
        ]);       
    }

    public function create()
    {
        return Inertia::render('Dashboard/DietaryPreferences/Create');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'diet_name' => ['required', 'string', 'max:255'],
            'diet_desc' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $dietCode = $this->generateDietCode($validated['diet_name']);
        // if code exists, then use counter number to make it unique
        $counter = 1;
        $originalCode = $dietCode;
        
        while (DietaryPreference::where('diet_code', $dietCode)->exists()) {
            $dietCode = $originalCode . '_' . $counter;
            $counter++;
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('dietary_preferences', 'public');
        }

        $dietaryPreference = DietaryPreference::create([
            'diet_name' => $validated['diet_name'],
            'diet_code' => $dietCode,
            'diet_desc' => $validated['diet_desc'],
            'image' => $imagePath,
        ]);

        return redirect('/dashboard/dietary-preferences')->with('flash', [
            'type' => 'success',
            'message' => 'Dietary preference created successfully.'
        ]);
    }

    public function edit(DietaryPreference $dietary_preference)
    {
        return Inertia::render('Dashboard/DietaryPreferences/Edit', [
            'dietary_preference' => $dietary_preference
        ]);
    }

    public function update(Request $request, DietaryPreference $dietary_preference){
        $validated = $request->validate([
            'diet_name' => ['required', 'string', 'max:255'],
            'diet_desc' => ['required', 'string', 'max:255'],
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
            if ($dietary_preference->image) {
                Storage::disk('public')->delete($dietary_preference->image);
            }
            $imagePath = $request->file('image')->store('dietary_preferences', 'public');
            $validated['image'] = $imagePath;
        }

        $dietCode = $this->generateDietCode($validated['diet_name']);
        // if code exists, then use counter number to make it unique
        $counter = 1;
        $originalCode = $dietCode;
        
        while (DietaryPreference::where('diet_code', $dietCode)->where('dietary_preference_id', '!=', $dietary_preference->dietary_preference_id)->exists()) {
            $dietCode = $originalCode . '_' . $counter;
            $counter++;
        }

        $dietary_preference->update([
            'diet_name' => $validated['diet_name'],
            'diet_desc' => $validated['diet_desc'],
            'diet_code' => $dietCode,
            'image' => $validated['image'] ?? $dietary_preference->image,
        ]);

        return redirect('/dashboard/dietary-preferences')->with('flash', [
            'type' => 'success',
            'message' => 'Dietary preference updated successfully.'
        ]);
    }

    public function destroy(DietaryPreference $dietary_preference)
    {
        $dietary_preference->delete();

        return redirect('/dashboard/dietary-preferences')->with('flash', [
            'type' => 'success',
            'message' => 'Dietary preference deleted successfully.'
        ]);
    }
}
