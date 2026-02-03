<?php

namespace App\Http\Controllers\Api;

use App\Models\Allergy;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

function normalizePhone($phone)
{
    if ($phone == null) return null;
    if ($phone) {
        $phone = preg_replace('/^(?:\+62|62|0)/', '', $phone);
        $phone = '+62' . $phone;
        return $phone;
    }
}

class OnboardingController extends Controller
{
    /**
     * Get onboarding status
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $user = $request->user()->load(['dietaryPreferences', 'allergies']);

        return response()->json([
            'success' => true,
            'data' => [
                'onboarding_completed' => $user->onboarding_completed,
                'user' => $user,
                'allergies' => Allergy::all(),
                'dietary_preferences' => DietaryPreference::all(),
            ]
        ]);
    }

    /**
     * Setup profile (step 1)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setupProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "full_name" => "required|string|max:50",
            "phone" => [
                "nullable",
                "regex:/^(?:\+62|62|0)?8\d{8,12}$/",
            ],
            "gender" => "nullable|in:male,female,silent",
            "birth_date" => "nullable|date|before:today",
        ], [
            "full_name.required" => "Full name is required",
            "full_name.max" => "Full name must not exceed 50 characters",
            "phone.regex" => "Invalid phone number format. Use Indonesian phone format (08xx)",
            "gender.in" => "Gender must be male, female, or silent",
            "birth_date.date" => "Invalid birth date format",
            "birth_date.before" => "Birth date must be before today",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $phone = normalizePhone($request->phone) ?? null;

        if ($phone) {
            // Additional phone uniqueness check
            $phoneValidator = Validator::make(['phone' => $phone], [
                'phone' => 'unique:users,phone,' . $request->user()->user_id . ',user_id',
            ], [
                'phone.unique' => 'Phone number already registered',
            ]);

            if ($phoneValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $phoneValidator->errors()
                ], 422);
            }
        }

        $user = $request->user();
        $user->update([
            'full_name' => $validated['full_name'] ?? $user->full_name,
            'phone' => $phone ?? $user->phone,
            'gender' => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup completed',
            'data' => $user
        ]);
    }

    /**
     * Setup dietary preferences (step 2)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setupDietaryPreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "preferences" => "nullable|array",
            "preferences.*" => "exists:dietary_preferences,dietary_preference_id",
        ], [
            "preferences.array" => "Preferences must be an array",
            "preferences.*.exists" => "Selected dietary preference does not exist",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = $request->user();
        $user->dietaryPreferences()->sync($validated['preferences'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Dietary preferences setup completed',
            'data' => $user->load('dietaryPreferences')
        ]);
    }

    /**
     * Setup allergies (step 3)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setupAllergies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "allergies" => "nullable|array",
            "allergies.*" => "exists:allergies,allergy_id",
        ], [
            "allergies.array" => "Allergies must be an array",
            "allergies.*.exists" => "Selected allergy does not exist",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = $request->user();
        $user->allergies()->sync($validated['allergies'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Allergies setup completed',
            'data' => $user->load('allergies')
        ]);
    }

    /**
     * Complete onboarding
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeOnboarding(Request $request)
    {
        $user = $request->user();

        if ($user->onboarding_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Onboarding already completed',
            ], 400);
        }

        $user->update(['onboarding_completed' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully! Welcome aboard!',
            'data' => $user->load(['dietaryPreferences', 'allergies'])
        ]);
    }
}