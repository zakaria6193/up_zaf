<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Support\QrStyle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $businessId = $request->query('business');
        if ($businessId) {
            $business = $user->businesses()->where('nanoid', $businessId)->firstOrFail();
        } else {
            $business = $user->businesses()->first();
        }

        if (! $business) {
            return redirect()->route('business.dashboard');
        }

        $hasLogo = filled($business->logo);

        return Inertia::render('Business/QRCode', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'color' => $business->color ?? '#4d54d9',
                'logo' => $business->logoUrl(),
                'qr_code' => $business->qrCodeUrl(),
                'qr_style' => $business->qrStyleCode(),
                'qr_label' => $business->qr_label,
                'qr_headline' => $business->qr_headline,
                'qr_label_default' => 'MENU',
                'qr_headline_default' => QrStyle::options()[$business->qrStyleCode()]['tagline'] ?? 'Discover the menu',
                'public_url' => $business->publicUrl(),
            ],
            'qrStyles' => QrStyle::forFrontend($hasLogo),
            'userBusinesses' => $user->businesses->map(fn ($b) => [
                'nanoid' => $b->nanoid,
                'name' => $b->name,
            ]),
        ]);
    }

    /**
     * Update the QR card design and regenerate the image.
     */
    public function updateStyle(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();
        $hasLogo = filled($business->logo);

        $validated = $request->validate([
            'qr_style' => ['required', 'string', Rule::in(QrStyle::codes($hasLogo))],
        ]);

        $business->update($validated);
        $business->generateQrCode();

        return back()->with('success', 'QR design updated.');
    }

    /**
     * Update guest-facing QR card text and regenerate the image.
     */
    public function updateText(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'qr_label' => ['nullable', 'string', 'max:24'],
            'qr_headline' => ['nullable', 'string', 'max:48'],
        ]);

        $business->update([
            'qr_label' => filled($validated['qr_label'] ?? null) ? trim($validated['qr_label']) : null,
            'qr_headline' => filled($validated['qr_headline'] ?? null) ? trim($validated['qr_headline']) : null,
        ]);
        $business->generateQrCode();

        return back()->with('success', 'QR text updated.');
    }
}
