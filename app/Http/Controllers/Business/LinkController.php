<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LinkController extends Controller
{
    /**
     * Display business links.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        // Get the business to manage (either from query param or first business)
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

        $links = $business->links;
        $activeLinksCount = $links->where('is_active', true)->count();

        return Inertia::render('Business/Links', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
            ],
            'links' => $links->map(fn ($link) => [
                'id' => $link->id,
                'type' => $link->type,
                'label' => $link->label,
                'url' => $link->url,
                'is_active' => $link->is_active,
                'order' => $link->order,
            ]),
            'link_types' => [
                'google_reviews' => 'Google Reviews',
                'google_maps' => 'Google Maps',
                'menu' => 'Menu',
                'instagram' => 'Instagram',
                'facebook' => 'Facebook',
                'whatsapp' => 'WhatsApp',
                'website' => 'Website',
                'link' => 'Custom Link',
            ],
            'can_add_more' => $activeLinksCount < 4,
            'userBusinesses' => $user->businesses->map(fn ($b) => [
                'nanoid' => $b->nanoid,
                'name' => $b->name,
            ]),
        ]);
    }

    /**
     * Store a new link.
     */
    public function store(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([
                'google_reviews',
                'google_maps',
                'menu',
                'instagram',
                'facebook',
                'whatsapp',
                'website',
                'link',
            ])],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        // Check if adding an active link and already at limit
        if ($validated['is_active'] ?? true) {
            $activeCount = $business->links()->where('is_active', true)->count();
            if ($activeCount >= 4) {
                return back()->withErrors(['is_active' => 'Maximum 4 active links allowed.']);
            }
        }

        // Get max order and add 1
        $maxOrder = $business->links()->max('order') ?? -1;
        $validated['order'] = $maxOrder + 1;

        $business->links()->create($validated);

        return back()->with('success', 'Link added successfully!');
    }

    /**
     * Update a link.
     */
    public function update(Request $request, string $nanoid, BusinessLink $link): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the link belongs to this business
        if ($link->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([
                'google_reviews',
                'google_maps',
                'menu',
                'instagram',
                'facebook',
                'whatsapp',
                'website',
                'link',
            ])],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        // Check if activating a link and already at limit
        if (($validated['is_active'] ?? true) && ! $link->is_active) {
            $activeCount = $business->links()->where('is_active', true)->count();
            if ($activeCount >= 4) {
                return back()->withErrors(['is_active' => 'Maximum 4 active links allowed.']);
            }
        }

        $link->update($validated);

        return back()->with('success', 'Link updated successfully!');
    }

    /**
     * Delete a link.
     */
    public function destroy(string $nanoid, BusinessLink $link): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        // Ensure the link belongs to this business
        if ($link->business_id !== $business->id) {
            abort(403);
        }

        $link->delete();

        return back()->with('success', 'Link deleted successfully!');
    }

    /**
     * Reorder links.
     */
    public function reorder(Request $request, string $nanoid): RedirectResponse
    {
        $user = auth()->user();
        $business = $user->businesses()->where('nanoid', $nanoid)->firstOrFail();

        $validated = $request->validate([
            'links' => ['required', 'array'],
            'links.*.id' => ['required', 'integer', 'exists:business_links,id'],
            'links.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['links'] as $linkData) {
            $link = BusinessLink::find($linkData['id']);

            // Ensure the link belongs to this business
            if ($link->business_id !== $business->id) {
                continue;
            }

            $link->update(['order' => $linkData['order']]);
        }

        return back()->with('success', 'Links reordered successfully!');
    }
}
