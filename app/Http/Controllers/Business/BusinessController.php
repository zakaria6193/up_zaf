<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Support\Currency;
use App\Support\QrStyle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class BusinessController extends Controller
{
    /**
     * Show the form for creating a new enterprise.
     */
    public function create(): Response|RedirectResponse
    {
        /** @var BusinessUser $user */
        $user = auth()->user();

        if (! $user->canCreateBusiness()) {
            return redirect()->route('business.dashboard')
                ->with('error', 'Free accounts can only create one business. Upgrade to Premium to add more.');
        }

        return Inertia::render('Business/Businesses/Create', [
            'currencies' => Currency::forFrontend(),
            'qrStyles' => QrStyle::forFrontend(),
            'subscription' => $user->subscriptionPayload(),
        ]);
    }

    /**
     * Store a newly created enterprise for the authenticated owner.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var BusinessUser $user */
        $user = auth()->user();

        if (! $user->canCreateBusiness()) {
            return redirect()->route('business.dashboard')
                ->with('error', 'Free accounts can only create one business. Upgrade to Premium to add more.');
        }

        $hasLogo = $request->hasFile('logo');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency' => ['required', 'string', 'size:3', Rule::in(Currency::codes())],
            'qr_style' => ['required', 'string', Rule::in(QrStyle::codes($hasLogo))],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->processLogo($request->file('logo'));
        }

        $validated['business_user_id'] = $user->id;
        $validated['is_active'] = true;

        $business = Business::create($validated);

        return redirect()->route('business.profile', ['business' => $business->nanoid])
            ->with('success', 'Business created successfully!');
    }

    /**
     * Process and optimize logo image for web/mobile.
     */
    private function processLogo($file): string
    {
        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodePath($file->getRealPath());
        $image->scale(width: 400, height: 400);
        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 85);
        $filename = 'logos/'.uniqid().'_'.time().'.jpg';
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
