<?php

namespace App\Http\Controllers\Api;

use App\Models\Allergy;
use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

function normalizePhone($phone)
{
    if ($phone == null) return null;
    if ($phone) {
        $phone = preg_replace('/^(?:\+62|62|0)/', '', $phone);
        $phone = '+62' . $phone;
        return $phone;
    }
}

class UserController extends Controller
{
    /**
     * Get user profile with liked recipes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['dietaryPreferences', 'allergies']);

        // Get user's liked recipes with pagination
        $likedRecipes = $user
            ->likedRecipes()
            ->withCount('likes')
            ->withExists([
                'likes as liked_by_me' => fn($q) => $q->where('user_id', $user->user_id),
            ])
            ->latest('like_recipes.liked_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'liked_recipes' => $likedRecipes,
            ]
        ]);
    }

    /**
     * Update user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => [
                "required",
                "min:3",
                "max:20",
                "unique:users,username," . $request->user()->user_id . ",user_id",
                "regex:/^[a-z0-9_.]+$/i",
            ],
            "email" => "required|email:rfc,dns|unique:users,email," . $request->user()->user_id . ",user_id",
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
        ], [
            "username.required" => "Username is required",
            "username.min" => "Username must be at least 3 characters",
            "username.max" => "Username must not exceed 20 characters",
            "username.unique" => "Username already taken",
            "username.regex" => "Username can only contains alphanumeric characters, underscore (_), and dot (.)",
            "email.required" => "Email is required",
            "email.email" => "Invalid email format",
            "email.unique" => "Email already registered",
            "bio.max" => "Bio must not exceed 1000 characters",
            "full_name.required" => "Full name is required",
            "full_name.max" => "Full name must not exceed 50 characters",
            "phone.regex" => "Invalid phone number format. Use Indonesian phone format (08xx)",
            "gender.in" => "Gender must be male, female, or silent",
            "birth_date.date" => "Invalid birth date format",
            "birth_date.before" => "Birth date must be before today",
            "dietary_preferences.array" => "Dietary preferences must be an array",
            "dietary_preferences.*.exists" => "Selected dietary preference does not exist",
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

        $phone = normalizePhone($request->phone);
        
        if ($phone) {
            // Additional phone uniqueness check after normalization
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
            'bio' => $validated['bio'] ?? $user->bio,
            'full_name' => $validated['full_name'],
            'gender' => $validated['gender'] ?? $user->gender,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $phone,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
        ]);

        $user->dietaryPreferences()->sync($validated['dietary_preferences'] ?? []);
        $user->allergies()->sync($validated['allergies'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->load(['dietaryPreferences', 'allergies'])
        ]);
    }

    /**
     * Update profile image
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ], [
            'profile_image.required' => 'Profile image is required',
            'profile_image.image' => 'File must be an image',
            'profile_image.mimes' => 'Image must be jpeg, jpg, png, or gif',
            'profile_image.max' => 'Image size must not exceed 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete old image
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profile-images', 'public');

        $user->update(['profile_image' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'data' => [
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image ? Storage::url($user->profile_image) : null,
            ]
        ]);
    }

    /**
     * Remove profile image
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeProfileImage(Request $request)
    {
        $user = $request->user();

        if (!$user->profile_image) {
            return response()->json([
                'success' => false,
                'message' => 'No profile image to remove'
            ], 400);
        }

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile image removed successfully'
        ]);
    }
}