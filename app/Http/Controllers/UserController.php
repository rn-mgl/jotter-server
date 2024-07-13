<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function show()
    {
        $user = User::find(Auth::id());

        return response()->json($user);
    }

    public function patch(Request $request)
    {
        $request->validate([
            "first_name" => ["required"],
            "last_name" => ["required"],
        ]);

        $profile_image = $request["existing_image"];

        if ($request->hasFile("image")) {
            $profile_image = cloudinary()->uploadFile($request->file("image")->getRealPath(), ["folder" => "jotter-uploads"])->getSecurePath();
        }

        $attributes = [
            "first_name" => $request["first_name"],
            "last_name" => $request["last_name"],
            "image" => $profile_image,
        ];

        $id = Auth::id();
        $user = User::find($id);

        $updated = $user->update($attributes);

        return response()->json(["success" => $updated]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            "current_password" => ["required"],
            "password" => ["required", "confirmed", Password::min(8)],
        ]);

        $userId = Auth::id();
        $user = User::find($userId);

        $isCorrectPassword = Hash::check($request["current_password"], $user->password);

        if (!$isCorrectPassword) {
            throw ValidationException::withMessages([
                "current_password" => "Your current password input does not match our record"
            ]);
        }

        $new_password = Hash::make($request["password"]);

        $attributes = [
            "password" => $new_password,
            "updated_at" => now()
        ];

        $updated = $user->update($attributes);

        return response()->json(["success" => $updated]);
    }
}
