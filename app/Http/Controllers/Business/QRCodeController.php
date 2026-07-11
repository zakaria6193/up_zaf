<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QRCodeController extends Controller
{
    /**
     * Display QR code page.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get the business (either from query param or first business)
        $businessId = $request->query('business');
        if ($businessId) {
            $business = $user->businesses()->where('nanoid', $businessId)->firstOrFail();
        } else {
            $business = $user->businesses()->first();
        }

        // If user has no businesses, redirect to dashboard
        if (! $business) {
            return redirect()->route('business.dashboard');
        }

        return Inertia::render('Business/QRCode', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'qr_code' => $business->qrCodeUrl(),
                'public_url' => $business->publicUrl(),
            ],
            'userBusinesses' => $user->businesses->map(fn ($b) => [
                'nanoid' => $b->nanoid,
                'name' => $b->name,
            ]),
        ]);
    }
}
