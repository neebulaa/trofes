<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Allergy;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardAllergyController extends Controller
{
    private function generateAllergyCode($allergyName)
    {
        $code = strtolower($allergyName);
        $code = preg_replace('/\s+/', '_', $code);
        $code = trim($code, '_');
        
        if (empty($code)) {
            $code = 'allergy_' . Str::random(6);
        }
        
        return $code;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 9);

        $query = Allergy::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('allergy_name', 'like', "%{$search}%")
                ->orWhere('allergy_code', 'like', "%{$search}%")
                ->orWhere('examples', 'like', "%{$search}%");
            });
        }

        $allergies = $query->paginate($perPage)->appends($request->only(['search', 'per_page']));

        return Inertia::render('Dashboard/Allergies/Index', [
            'allergies' => $allergies
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Allergies/Create');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'allergy_name' => ['required', 'string', 'max:255'],
            'examples' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $allergyCode = $this->generateAllergyCode($validated['allergy_name']);
        // if code exists, then use counter number to make it unique
        $counter = 1;
        $originalCode = $allergyCode;
        
        while (Allergy::where('allergy_code', $allergyCode)->exists()) {
            $allergyCode = $originalCode . '_' . $counter;
            $counter++;
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('allergies', 'public');
        }

        $allergy = Allergy::create([
            'allergy_name' => $validated['allergy_name'],
            'allergy_code' => $allergyCode,
            'examples' => $validated['examples'] ?? null,
            'image' => $imagePath,
        ]);

        return redirect('/dashboard/allergies')->with('flash', [
            'type' => 'success',
            'message' => 'Allergy created successfully.'
        ]);
    }

    public function edit(Allergy $allergy){
        return Inertia::render('Dashboard/Allergies/Edit', [
            'allergy' => $allergy,
        ]);
    }

    public function update(Request $request, Allergy $allergy){
        $validated = $request->validate([
            'allergy_name' => ['required', 'string', 'max:255'],
            'examples' => ['nullable', 'string', 'max:255'],
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
            if ($allergy->image) {
                Storage::disk('public')->delete($allergy->image);
            }
            $imagePath = $request->file('image')->store('allergies', 'public');
            $validated['image'] = $imagePath;
        }

        $allergyCode = $this->generateAllergyCode($validated['allergy_name']);
        // if code exists, then use counter number to make it unique
        $counter = 1;
        $originalCode = $allergyCode;
        
        while (Allergy::where('allergy_code', $allergyCode)->exists()) {
            $allergyCode = $originalCode . '_' . $counter;
            $counter++;
        }

        $allergy->update([
            'allergy_name' => $validated['allergy_name'],
            'examples' => $validated['examples'] ?? null,
            'allergy_code' => $allergyCode,
            'image' => $validated['image'] ?? $allergy->image,
        ]);

        return redirect('/dashboard/allergies')->with('flash', [
            'type' => 'success',
            'message' => 'Allergy updated successfully.'
        ]);
    }

    public function destroy(Allergy $allergy){
        $allergy->delete();

        return redirect('/dashboard/allergies')->with('flash', [
            'type' => 'success',
            'message' => 'Allergy deleted successfully.'
        ]);
    }
}
