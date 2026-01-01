<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

function usernameToFullName(string $username): string
{
    $name = str_replace(['_', '.'], ' ', $username);
    // removing trailing numbers (like john99 to john)
    $name = preg_replace('/\d+$/', '', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name));
    return Str::title($name);
}

class AuthController extends Controller
{
    public function login(){
        return Inertia::render('Login');
    }

    public function signup(){
        return Inertia::render('SignUp');
    }

    public function register(Request $request){
        $validated = $request->validate([
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
            "remember" => ["sometimes", "boolean"],
        ], [
            "username.regex" => "Username can only contains alphanumeric characters, underscore (_), and dot (.)",
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            "full_name" => usernameToFullName($validated['username']),
        ]);

        $remember = $request->boolean('remember');
        Auth::login($user, $remember);

        return redirect('/onboarding');
    }

    public function authenticate(Request $request){
        $request->validate([
            "login" => "required",
            "password" => "required",
            "remember" => "sometimes|boolean",
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->with('flash', [
                'type' => 'error', // three category yaa ada error, success, info
                'message' => 'The credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    public function signOut(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function forgotPassword(){
        return Inertia::render('ForgotPassword', [
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
    }

    public function sendResetLink(Request $request){
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Account not found.',
        ]);

        $status = Password::sendResetLink(['email' => $validated['email']]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('flash', [
                'type' => 'success',
                'message' => __($status),
            ]);
        }

        return back()->with('flash', [
            'type' => 'error',
            'message' => __($status),
        ]); 
    }

    public function resetPassword($token){
        $user = User::where('email', request('email'))->first();
        return Inertia::render('ResetPassword', [
            'token' => $token,
            'email' => request('email'),
            'username' => $user->username
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', new StrongPassword, 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // $user->update([
                //     'password' => $request->password,
                //     'remember_token' => Str::random(60),
                // ]);
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('flash', [
                'type' => 'success',
                'message' => __($status),
            ]);
        }

        return back()->with('flash', [
            'type' => 'error',
            'message' => __($status),
        ]);
    }
}
