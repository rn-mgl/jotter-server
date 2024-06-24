<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate([
            "first_name" => ["required"],
            "last_name" => ["required"],
            "email" => ["required", "email", "unique:users,email"],
            "password" => ["required", Password::min(8), "confirmed"],
        ]);

        $user = User::create($attributes);

        event(new Registered($user));

        Auth::login($user);

        return response()->json(["success" => true]);
    }
}
