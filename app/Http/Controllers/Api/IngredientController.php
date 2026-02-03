<?php

namespace App\Http\Controllers\Api;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    /**
     * Get all ingredients with search and pagination
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
        $perPage = $request->query('per_page', 50);

        $query = Ingredient::query();

        if ($search) {
            $query->where('ingredient_name', 'like', "%{$search}%");
        }

        $ingredients = $query->orderBy('ingredient_name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ingredients
        ]);
    }

    /**
     * Get single ingredient with related recipes
     * 
     * @param Ingredient $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ingredient $ingredient)
    {
        // Load related recipes
        $ingredient->load(['recipes' => function($query) {
            $query->withCount('likes')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $ingredient
        ]);
    }

    /**
     * Get popular ingredients (most used in recipes)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular()
    {
        $ingredients = Ingredient::withCount('recipes')
            ->orderByDesc('recipes_count')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ingredients
        ]);
    }

    /**
     * Admin: Create ingredient
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredient_name' => 'required|string|max:255|unique:ingredients,ingredient_name',
        ], [
            'ingredient_name.required' => 'Ingredient name is required',
            'ingredient_name.max' => 'Ingredient name must not exceed 255 characters',
            'ingredient_name.unique' => 'Ingredient name already exists',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $ingredient = Ingredient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ingredient created successfully',
            'data' => $ingredient
        ], 201);
    }

    /**
     * Admin: Update ingredient
     * 
     * @param Request $request
     * @param Ingredient $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $validator = Validator::make($request->all(), [
            'ingredient_name' => 'required|string|max:255|unique:ingredients,ingredient_name,' . $ingredient->ingredient_id . ',ingredient_id',
        ], [
            'ingredient_name.required' => 'Ingredient name is required',
            'ingredient_name.max' => 'Ingredient name must not exceed 255 characters',
            'ingredient_name.unique' => 'Ingredient name already exists',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $ingredient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ingredient updated successfully',
            'data' => $ingredient
        ]);
    }

    /**
     * Admin: Delete ingredient
     * 
     * @param Ingredient $ingredient
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ingredient $ingredient)
    {
        // Check if ingredient is being used in recipes
        $recipesCount = $ingredient->recipes()->count();

        if ($recipesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete ingredient. It is currently used in {$recipesCount} recipe(s)."
            ], 400);
        }

        $ingredient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ingredient deleted successfully'
        ]);
    }
}