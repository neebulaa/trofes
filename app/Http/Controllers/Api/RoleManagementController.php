<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RoleManagementController extends Controller
{
    /**
     * Get all users with roles
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:admin,user',
            'per_page' => 'nullable|integer|min:1|max:100',
        ], [
            'search.string' => 'Search must be a string',
            'search.max' => 'Search query too long',
            'role.in' => 'Role must be admin or user',
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
        $role = $request->query('role');
        $perPage = $request->query('per_page', 20);

        $query = User::select([
            'user_id', 
            'username', 
            'email', 
            'full_name', 
            'role', 
            'created_at',
            'onboarding_completed'
        ]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
            'available_roles' => ['admin', 'user']
        ]);
    }

    /**
     * Assign role to user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:admin,user',
        ], [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be admin or user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::findOrFail($validated['user_id']);

        // Prevent user from changing their own role
        if ($user->user_id === $request->user()->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own role.'
            ], 403);
        }

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        return response()->json([
            'success' => true,
            'message' => "User role changed from '{$oldRole}' to '{$validated['role']}' successfully.",
            'data' => $user
        ]);
    }
}