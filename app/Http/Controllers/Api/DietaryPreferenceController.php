<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\DietaryPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DietaryPreferenceController extends Controller
{
    /**
     * Get all dietary preferences
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $preferences = DietaryPreference::all();

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Get single dietary preference
     * 
     * @param DietaryPreference $dietary_preference
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(DietaryPreference $dietary_preference)
    {
        // Load recipes that match this dietary preference
        $dietary_preference->load(['recipes' => function($query) {
            $query->withCount('likes')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $dietary_preference
        ]);
    }

    /**
     * Admin: Create dietary preference
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'diet_name' => 'required|string|max:255|unique:dietary_preferences,diet_name',
            'description' => 'nullable|string',
        ], [
            'diet_name.required' => 'Dietary preference name is required',
            'diet_name.max' => 'Dietary preference name must not exceed 255 characters',
            'diet_name.unique' => 'Dietary preference name already exists',
            'description.string' => 'Description must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $preference = DietaryPreference::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dietary preference created successfully',
            'data' => $preference
        ], 201);
    }

    /**
     * Admin: Update dietary preference
     * 
     * @param Request $request
     * @param DietaryPreference $dietary_preference
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, DietaryPreference $dietary_preference)
    {
        $validator = Validator::make($request->all(), [
            'diet_name' => 'required|string|max:255|unique:dietary_preferences,diet_name,' . $dietary_preference->dietary_preference_id . ',dietary_preference_id',
            'description' => 'nullable|string',
        ], [
            'diet_name.required' => 'Dietary preference name is required',
            'diet_name.max' => 'Dietary preference name must not exceed 255 characters',
            'diet_name.unique' => 'Dietary preference name already exists',
            'description.string' => 'Description must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $dietary_preference->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dietary preference updated successfully',
            'data' => $dietary_preference
        ]);
    }

    /**
     * Admin: Delete dietary preference
     * 
     * @param DietaryPreference $dietary_preference
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DietaryPreference $dietary_preference)
    {
        // Check if dietary preference is being used
        $usersCount = $dietary_preference->users()->count();
        $recipesCount = $dietary_preference->recipes()->count();

        if ($usersCount > 0 || $recipesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete dietary preference. It is currently assigned to {$usersCount} user(s) and {$recipesCount} recipe(s)."
            ], 400);
        }

        $dietary_preference->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dietary preference deleted successfully'
        ]);
    }
}