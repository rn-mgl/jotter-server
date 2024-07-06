<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("login", function() {
    return redirect("http://localhost:3000/login");
})->name("login");

Route::get("csrf_token", function() {
    return response()->json(["csrf_token" => csrf_token()]);
});

Route::controller(RegisterController::class)->group(function() {
    Route::post("/register", "store");
});

Route::controller(SessionController::class)->group(function() {
    Route::post("login", "store");
});

Route::get("get_user", function() {
    $user = request()->header("user");
    $decrypt = Crypt::decryptString($user);
    $user = User::find($decrypt);
    return response()->json(["valid" => $user ? true : false]);
});

Route::middleware(["auth"])->group(function() {
    Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect("http://localhost:3000/login");
    })->middleware(['signed'])->name('verification.verify');

    Route::post('email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(["success" => true]);
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::controller(NoteController::class)->group(function() {
        Route::get("note", "index");
        Route::get("note/{note}", "show")->can("edit", "note");
        Route::post("note", "store");
        Route::patch("note/{note}", "patch")->can("edit", "note");
        Route::delete("note/{note}", "destroy")->can("edit", "note");
    });

    Route::controller(SessionController::class)->group(function() {
        Route::delete("logout", "destroy");
    });

    Route::controller(UserController::class)->group(function() {
        Route::get("profile", "show");
        Route::patch("profile", "patch");
    });
});
