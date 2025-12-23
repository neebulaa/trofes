<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            "full_name" => "required|string|min:2",

            "username" => [
                "required",
                "min:3",
                "max:20",
                "unique:users,username",
                "regex:/^[a-z0-9_.]+$/i",
            ],

            "email" => "required|email:rfc,dns|unique:users,email",
            "password" => "required|min:6|confirmed",
            // the 'confirmed' rule make sure 'password_confirmation' is passed in request
            "password_confirmation" => "required"
        ], [
            "username.regex" => "Username can only contains alphanumeric characters, underscore (_), and dot (.)"
        ]);

        if ($validator->fails()) {
            return response([
                "message" => "Invalid fields",
                "errors" => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $user = User::create($data);
        Auth::login($user);

        $token = $user->createToken(uniqid())->plainTextToken;

        return response([
            "message" => "Register success",
            "token" => $token,
            "user" => $user
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            "login" => "required",
            "password" => "required|min:6",
        ]);

        if ($validator->fails()) {
            return response([
                "message" => "Invalid fields",
                "errors" => $validator->errors()
            ], 422);
        }

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials)) {
            return response([
                "message" => "Wrong Email/Username or password."
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken(uniqid())->plainTextToken;

        return response([
            "message" => "Login Success",
            "token" => $token,
            "user" => $user
        ], 201);
    }
    
    public function logout(){
        auth()->user()->tokens()->delete();
        return response([
            "message" => "Logout Success"
        ]);
    }

    public function me(){
        return response([
            "message" => "Get user success",
            "user" => auth()->user()
        ]);
    }
}
