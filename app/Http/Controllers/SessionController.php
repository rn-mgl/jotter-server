<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $user = Auth::user();
        $encryptedUser = Crypt::encryptString($user->id);
        $isVerified = $user->email_verified_at;

        if (!$isVerified) {
            $request->user()->sendEmailVerificationNotification();
        }

        $request->session()->regenerate();

        return response()->json(["success" => true, "user" => $encryptedUser, "isVerified" => $isVerified]);

    }

    public function getLoggedUser()
    {
        $user = auth()->user();
        $encryptedUser = Crypt::encryptString($user->id);

        return response()->json(["user" => $encryptedUser]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(["success" => true]);
    }
}
