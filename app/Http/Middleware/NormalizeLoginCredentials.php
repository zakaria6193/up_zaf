<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeLoginCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If phone is provided but not email, use phone as the email field for Fortify
        if ($request->filled('phone') && ! $request->filled('email')) {
            $request->merge(['email' => $request->input('phone')]);
        }

        return $next($request);
    }
}
