<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            logger($request->url());
            $auth = $request->headers->get("Authorization");

            if (!$auth || !Str::startsWith($auth, "Bearer")) {
                throw new Exception("Unauthorized entry. Please log in first.");
            }

            $encrypted = explode(" ", $auth)[1];

            $decrypted = Crypt::decryptString($encrypted);

            if (Auth::id() !== intval($decrypted)) {
                throw new Exception("Unauthorized entry. Please log in first.");
            }

            return $next($request);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }
}
