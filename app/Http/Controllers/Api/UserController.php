<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            "full_name" => "required|string|max:50",

            "username" => [
                "required",
                "min:3",
                "max:20",
                "unique:users,username",
                "regex:/^[a-z0-9_.]+$/i",
            ],

            "email" => "required|email:rfc,dns|unique:users,email",
            "password" => "required|min:6|confirmed",
            // make sure 'password_confirmation' is passed in request

            "phone" => [
                "required",
                "regex:/^(\+62|62|0)(\d{8,13})$/", 
                // +62 or 62 or 0, then 8-13 digits
                "unique:users,phone"
            ],
            "gender" => "required|in:male,female,other",
            "birth_date" => "required|date|before:today",
            "bio" => "nullable|max:100"
        ], [
            "username.regex" => "Username can only contains alphanumeric characters, underscore (_), and dot (.)"
        ]);
    }
}
