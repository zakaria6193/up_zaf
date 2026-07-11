<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is from User model (admins/managers)
        if (! $request->user() || ! ($request->user() instanceof \App\Models\User)) {
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin area.');
        }

        return $next($request);
    }
}
