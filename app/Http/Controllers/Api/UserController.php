<?php

namespace App\Http\Controllers\Api;

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

class UserController extends Controller
{
    public function index(){
        return Inertia::render('Profile', [
            'user' => Auth::user()->load(['dietaryPreferences', 'allergies',  'likedRecipes']),
            'dietary_preferences' => DietaryPreference::all(),
            'allergies' => Allergy::all(),
        ]);
    }

    public function update(Request $request){
        $request->validate([
            "username" => [
                "required",
                "min:3",
                "max:20",
                "unique:users,username," . Auth::id() . ",user_id",
                "regex:/^[a-z0-9_.]+$/i",
            ],
            "email" => "required|email:rfc,dns|unique:users,email," . Auth::id() . ",user_id",
            'bio' => 'nullable|string|max:1000',
            "full_name" => "required|string|max:50",
            "phone" => [
                "nullable",
                "regex:/^(?:\+62|62|0)?8\d{8,12}$/",
            ],
            "gender" => "nullable|in:male,female,silent",
            "birth_date" => "nullable|date|before:today",
            "dietary_preferences" => "nullable|array",
            "dietary_preferences.*" => "exists:dietary_preferences,dietary_preference_id",
            "allergies" => "nullable|array",
            "allergies.*" => "exists:allergies,allergy_id",
        ]);

        $phone = normalizePhone($request->phone);
        // to check unique phone number after normalization bruh
        if ($phone) {
            $request->merge(['phone' => $phone]);

            $request->validate([
                'phone' => 'unique:users,phone,' . Auth::id() . ',user_id',
            ]);
        }

        $user = Auth::user();
        $user->update([
            'bio'         => $request->bio,
            'full_name'   => $request->full_name,
            'gender'      => $request->gender ?? $user->gender, 
            
            'username'    => $request->username,
            'email'       => $request->email,
            'phone'       => $phone,
            'birth_date'  => $request->birth_date ?? $user->birth_date,
        ]);

        $user->dietaryPreferences()->sync($request->dietary_preferences ?? []);
        $user->allergies()->sync($request->allergies ?? []);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Profile updated successfully.'
        ]);
    }
}
