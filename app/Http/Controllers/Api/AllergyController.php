<?php

namespace App\Http\Controllers\Api;

use App\Models\Allergy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AllergyController extends Controller
{
    /**
     * Get all allergies
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $allergies = Allergy::all();

        return response()->json([
            'success' => true,
            'data' => $allergies
        ]);
    }

    /**
     * Get single allergy
     * 
     * @param Allergy $allergy
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Allergy $allergy)
    {
        // Load recipes that contain this allergy
        $allergy->load(['recipes' => function($query) {
            $query->withCount('likes')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $allergy
        ]);
    }

    /**
     * Admin: Create allergy
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'allergy_name' => 'required|string|max:255|unique:allergies,allergy_name',
            'description' => 'nullable|string',
        ], [
            'allergy_name.required' => 'Allergy name is required',
            'allergy_name.max' => 'Allergy name must not exceed 255 characters',
            'allergy_name.unique' => 'Allergy name already exists',
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

        $allergy = Allergy::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Allergy created successfully',
            'data' => $allergy
        ], 201);
    }

    /**
     * Admin: Update allergy
     * 
     * @param Request $request
     * @param Allergy $allergy
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Allergy $allergy)
    {
        $validator = Validator::make($request->all(), [
            'allergy_name' => 'required|string|max:255|unique:allergies,allergy_name,' . $allergy->allergy_id . ',allergy_id',
            'description' => 'nullable|string',
        ], [
            'allergy_name.required' => 'Allergy name is required',
            'allergy_name.max' => 'Allergy name must not exceed 255 characters',
            'allergy_name.unique' => 'Allergy name already exists',
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

        $allergy->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Allergy updated successfully',
            'data' => $allergy
        ]);
    }

    /**
     * Admin: Delete allergy
     * 
     * @param Allergy $allergy
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Allergy $allergy)
    {
        // Check if allergy is being used by users
        $usersCount = $allergy->users()->count();
        $recipesCount = $allergy->recipes()->count();

        if ($usersCount > 0 || $recipesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete allergy. It is currently assigned to {$usersCount} user(s) and {$recipesCount} recipe(s)."
            ], 400);
        }

        $allergy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Allergy deleted successfully'
        ]);
    }
}