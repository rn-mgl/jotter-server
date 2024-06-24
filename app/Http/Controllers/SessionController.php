<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required"]
        ]);

        if (!Auth::attempt($attributes) ){
            throw ValidationException::withMessages([
                "email" => "Incorrect credentials"
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $encryptedUser = Crypt::encryptString($user->id);
        $isVerified = $user->email_verified_at;

        if (!$isVerified) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json(["success" => true, "user" => $encryptedUser, "isVerified" => $isVerified]);

    }
}
