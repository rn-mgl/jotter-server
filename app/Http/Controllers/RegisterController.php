<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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


    public function forgotPassword(Request $request)
    {
        $registeredEmail = $request->input("registered_email");

        $request->validate([
            "registered_email" => ["required", "email", "exists:users,email"]
        ]);

        $status = FacadesPassword::sendResetLink([
            "email" => $registeredEmail
        ]);

        if ($status !== FacadesPassword::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                "registered_email" => "Could not send password reset link. Review your input email"
            ]);
        }

        return response()->json(["status" => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            "token" => ["required"],
            "email" => ["required", "email", "exists:users,email"],
            "password" => ["required", "confirmed"]
        ]);

        $status = FacadesPassword::reset(
            $request->only("email", "password", "password_confirmation", "token"),

            function(User $user, $password) {
                $user->forceFill([
                    "password" => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json(["status" => $status === "passwords.reset"]);

    }
}
