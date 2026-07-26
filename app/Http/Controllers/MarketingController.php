<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MarketingController extends Controller
{
    /**
     * Marketing landing page for business owners.
     */
    public function home(): Response|RedirectResponse
    {
        if (auth()->check()) {
            if (auth()->user() instanceof User) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('business.dashboard');
        }

        return Inertia::render('Marketing/Landing', [
            'googleEnabled' => filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret')),
            'trialMinutes' => (int) config('business.free_trial_minutes', 10),
        ]);
    }
}
