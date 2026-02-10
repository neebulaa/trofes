<?php

namespace App\Http\Controllers;

use App\Models\User;

use Inertia\Inertia;
use App\Models\Allergy;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

function normalizePhone($phone){
    if($phone == null) return null;
    if ($phone) {
        $phone = preg_replace('/^(?:\+62|62|0)/', '', $phone);
        $phone = '+62' . $phone;
        return $phone;
    }
}

class OnboardingController extends Controller
{
    public function setupProfile(Request $request)
    {
        $validated = $request->validate([
            "full_name" => "required|string|max:50",
            "phone" => [
                "nullable",
                "regex:/^(?:\+62|62|0)?8\d{8,12}$/",
            ],
            "gender" => "nullable|in:male,female,silent",
            "birth_date" => "nullable|date|before:today",
        ]);

        $phone = normalizePhone($request->phone) ?? null;
        // to check unique phone number after normalization bruh
        if ($phone) {
            $request->merge(['phone' => $phone]);

            $request->validate([
                'phone' => 'unique:users,phone,' . Auth::id() . ',user_id',
            ]);
        }

        $user = Auth::user();
        $user->update([
            'full_name' => $validated['full_name'] ?? $user->full_name,
            'phone' => $phone ?? $user->phone,
            'gender' => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
        ]);

        return back();
    }

    public function setupDietaryPreferences(Request $request)
    {
        $validated = $request->validate([
            "preferences" => "nullable|array",
            "preferences.*" => "exists:dietary_preferences,dietary_preference_id",
        ]);

        
        $user = Auth::user();
        $user->dietaryPreferences()->sync($validated['preferences'] ?? []);

        return back();
    }

    public function setupAllergies(Request $request)
    {
        $validated = $request->validate([
            "allergies" => "nullable|array",
            "allergies.*" => "exists:allergies,allergy_id",
        ]);

        $user = Auth::user();
        $user->allergies()->sync($validated['allergies'] ?? []);
        return back();
    }

    public function completeOnboarding()
    {
        $user = Auth::user();
        $user->update([
            'onboarding_completed' => true,
        ]);

        return redirect('/');
    }

    public function onboarding(){
        return Inertia::render('Onboarding', [
            'allergies' => Allergy::all(),
            'user_allergies' => Auth::user()->allergies->pluck('allergy_id')->toArray(),
            'user_dietary_preferences' => Auth::user()->dietaryPreferences->pluck('dietary_preference_id')->toArray(),
            'dietary_preferences' => DietaryPreference::all(),
            'user' => Auth::user(),
        ]);
    }
}
