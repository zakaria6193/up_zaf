<?php

namespace App\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        // Check if user is an admin (from users table)
        if ($user instanceof \App\Models\User) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Otherwise it's a business user (from business_users table)
        return redirect()->intended(route('business.dashboard'));
    }
}
