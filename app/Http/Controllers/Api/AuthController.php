<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

function usernameToFullName(string $username): string
{
    $name = str_replace(['_', '.'], ' ', $username);
    $name = preg_replace('/\d+$/', '', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name));
    return Str::title($name);
}

class AuthController extends Controller
{
    /**
     * Register new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => [
                "required",
                "min:3",
                "max:20",
                "unique:users,username",
                "regex:/^[a-z0-9_.]+$/i",
            ],
            "email" => ["required", "email:rfc,dns", "unique:users,email"],
            "password" => ["required", new StrongPassword, "confirmed"],
            "password_confirmation" => ["required"],
        ], [
            "username.regex" => "Username can only contains alphanumeric characters, underscore (_), and dot (.)",
            "username.required" => "Username is required",
            "username.min" => "Username must be at least 3 characters",
            "username.max" => "Username must not exceed 20 characters",
            "username.unique" => "Username already taken",
            "email.required" => "Email is required",
            "email.email" => "Invalid email format",
            "email.unique" => "Email already registered",
            "password.required" => "Password is required",
            "password.confirmed" => "Password confirmation does not match",
            "password_confirmation.required" => "Password confirmation is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            "full_name" => usernameToFullName($validated['username']),
        ]);

        // Create token for mobile
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please complete your onboarding.',
            'data' => [
                'user' => $user->load(['dietaryPreferences', 'allergies']),
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "login" => "required",
            "password" => "required",
        ], [
            "login.required" => "Email or username is required",
            "password.required" => "Password is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($loginField, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'login' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load(['dietaryPreferences', 'allergies']),
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get authenticated user info
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user()->load(['dietaryPreferences', 'allergies']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Logout user (revoke current token)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Send password reset link
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $status = Password::sendResetLink(['email' => $validated['email']]);

        // Prevent email enumeration - always return success
        if ($status === Password::RESET_LINK_SENT || $status === Password::INVALID_USER) {
            return response()->json([
                'success' => true,
                'message' => 'If your email exists in our system, you will receive a password reset link.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status)
        ], 400);
    }

    /**
     * Reset password with token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', new StrongPassword, 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'token.required' => 'Reset token is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'password_confirmation.required' => 'Password confirmation is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
            'errors' => [
                'email' => [__($status)]
            ]
        ], 400);
    }
}