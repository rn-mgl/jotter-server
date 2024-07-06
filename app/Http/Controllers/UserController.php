<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show()
    {
        $user = User::find(Auth::id());

        return response()->json($user);
    }

    public function patch(Request $request)
    {
        logger($request);
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
}
