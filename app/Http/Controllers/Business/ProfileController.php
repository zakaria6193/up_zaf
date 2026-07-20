<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    /**
     * Display business profile edit page.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get the business to edit (either from query param or first business)
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

        return Inertia::render('Business/Profile', [
            'business' => [
                'id' => $business->id,
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'lat' => $business->lat !== null ? (float) $business->lat : null,
                'lng' => $business->lng !== null ? (float) $business->lng : null,
                'logo' => $business->logoUrl(),
                'color' => $business->color ?? '#3b82f6',
                'is_active' => $business->is_active,
                'seo_title' => $business->seo_title,
                'seo_description' => $business->seo_description,
                'seo_keywords' => $business->seo_keywords,
                'qr_code' => $business->qrCodeUrl(),
                'public_url' => $business->publicUrl(),
            ],
            'userBusinesses' => $user->businesses->map(fn ($b) => [
                'nanoid' => $b->nanoid,
                'name' => $b->name,
            ]),
            'googleMapsApiKey' => config('services.google_maps.api_key'),
        ]);
    }

    /**
     * Update business profile.
     */
    public function update(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }

            $validated['logo'] = $this->processLogo($request->file('logo'));
        }

        $business->update($validated);

        // Regenerate QR code if URL-related data changed
        if (isset($validated['name'])) {
            $business->generateQrCode();
        }

        return back()->with('success', 'Business profile updated successfully!');
    }

    /**
     * Delete logo.
     */
    public function deleteLogo(string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        if ($business->logo) {
            Storage::disk('public')->delete($business->logo);
            $business->update(['logo' => null]);
        }

        return back()->with('success', 'Logo deleted successfully!');
    }

    /**
     * Process and optimize logo image for web/mobile.
     */
    private function processLogo($file): string
    {
        $manager = ImageManager::usingDriver(Driver::class);

        // Read the uploaded image
        $image = $manager->decodePath($file->getRealPath());

        // Resize to max 400x400 while maintaining aspect ratio
        $image->scale(width: 400, height: 400);

        // Encode with quality optimization (85% quality for good balance)
        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 85);

        // Generate unique filename
        $filename = 'logos/'.uniqid().'_'.time().'.jpg';

        // Save to storage
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
