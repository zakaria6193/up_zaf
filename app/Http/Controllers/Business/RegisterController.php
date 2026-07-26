<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Show the business owner registration form.
     */
    public function create(): Response|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('business.dashboard');
        }

        return Inertia::render('Business/Register', [
            'googleEnabled' => filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret')),
            'trialMinutes' => (int) config('business.free_trial_minutes', 10),
        ]);
    }

    /**
     * Register a new free business owner account (starts trial).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:business_users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:business_users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = BusinessUser::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(max(1, (int) config('business.free_trial_minutes', 10))),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('business.dashboard')
            ->with('success', 'Welcome! Your free trial has started.');
    }
}
