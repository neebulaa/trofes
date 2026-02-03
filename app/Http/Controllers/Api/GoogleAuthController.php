<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Login atau register menggunakan Google ID Token
     * Untuk Android, kirim ID Token dari Google Sign-In
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ], [
            'id_token.required' => 'Google ID token is required',
            'id_token.string' => 'Google ID token must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify ID token dengan Google
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google ID token'
                ], 401);
            }

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $avatar = $payload['picture'] ?? null;

            // Cek apakah user sudah ada
            $user = User::where('email', $email)
                ->orWhere('google_id', $googleId)
                ->first();

            if (!$user) {
                // Create new user
                $username = $this->generateUniqueUsername($email);
                
                $user = User::create([
                    'google_id' => $googleId,
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $name,
                    'password' => Hash::make(Str::random(32)), // Random password
                    'email_verified_at' => now(),
                    'profile_image' => $avatar,
                ]);
            } else {
                // Update google_id jika belum ada
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleId]);
                }
                
                // Update avatar if not set
                if (!$user->profile_image && $avatar) {
                    $user->update(['profile_image' => $avatar]);
                }
            }

            // Delete old tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => $user->load(['dietaryPreferences', 'allergies']),
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'errors' => [
                    'google' => [$e->getMessage()]
                ]
            ], 500);
        }
    }

    /**
     * Alternative: Login dengan access token (jika pakai Socialite)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithGoogleAccessToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ], [
            'access_token.required' => 'Google access token is required',
            'access_token.string' => 'Google access token must be a string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->access_token);

            $user = User::where('email', $googleUser->getEmail())
                ->orWhere('google_id', $googleUser->getId())
                ->first();

            if (!$user) {
                $username = $this->generateUniqueUsername($googleUser->getEmail());
                
                $user = User::create([
                    'google_id' => $googleUser->getId(),
                    'username' => $username,
                    'email' => $googleUser->getEmail(),
                    'full_name' => $googleUser->getName(),
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'profile_image' => $googleUser->getAvatar(),
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
                
                if (!$user->profile_image && $googleUser->getAvatar()) {
                    $user->update(['profile_image' => $googleUser->getAvatar()]);
                }
            }

            $user->tokens()->delete();
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => $user->load(['dietaryPreferences', 'allergies']),
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'errors' => [
                    'google' => [$e->getMessage()]
                ]
            ], 500);
        }
    }

    /**
     * Generate unique username from email
     * 
     * @param string $email
     * @return string
     */
    private function generateUniqueUsername($email)
    {
        $baseUsername = explode('@', $email)[0];
        $baseUsername = preg_replace('/[^a-z0-9_.]/', '', strtolower($baseUsername));
        
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Unlink Google account
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkGoogle(Request $request)
    {
        $user = $request->user();

        if (!$user->google_id) {
            return response()->json([
                'success' => false,
                'message' => 'Google account not linked'
            ], 400);
        }

        // Check if user has password (can still login with email/password)
        if (!$user->password || Hash::check('', $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot unlink Google account. Please set a password first to maintain account access.'
            ], 400);
        }

        $user->update(['google_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Google account unlinked successfully'
        ]);
    }
}