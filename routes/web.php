<?php

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
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
});
