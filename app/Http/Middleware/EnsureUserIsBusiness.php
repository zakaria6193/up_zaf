<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is from BusinessUser model
        if (! $request->user() || ! ($request->user() instanceof \App\Models\BusinessUser)) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in as a business owner to access this area.');
        }

        return $next($request);
    }
}
