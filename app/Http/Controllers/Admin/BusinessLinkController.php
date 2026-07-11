<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BusinessLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Business $business): Response
    {
        $business->load('links');

        return Inertia::render('Admin/Businesses/Links/Index', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
            ],
            'links' => $business->links->map(fn ($link) => [
                'id' => $link->id,
                'type' => $link->type,
                'label' => $link->label,
                'url' => $link->url,
                'is_active' => $link->is_active,
                'order' => $link->order,
            ]),
            'link_types' => BusinessLink::TYPES,
            'can_add_more' => $business->links()->where('is_active', true)->count() < 4,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Business $business): RedirectResponse
    {
        $activeLinksCount = $business->links()->where('is_active', true)->count();

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(BusinessLink::TYPES))],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['is_active'] && $activeLinksCount >= 4) {
            return back()->withErrors([
                'is_active' => 'You can only have a maximum of 4 active links.',
            ]);
        }

        $maxOrder = $business->links()->max('order') ?? -1;
        $validated['order'] = $maxOrder + 1;

        $business->links()->create($validated);

        return redirect()->route('admin.businesses.links.index', $business)
            ->with('success', 'Link added successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business, BusinessLink $link): RedirectResponse
    {
        $activeLinksCount = $business->links()
            ->where('is_active', true)
            ->where('id', '!=', $link->id)
            ->count();

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(BusinessLink::TYPES))],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['is_active'] && $activeLinksCount >= 4) {
            return back()->withErrors([
                'is_active' => 'You can only have a maximum of 4 active links.',
            ]);
        }

        $link->update($validated);

        return redirect()->route('admin.businesses.links.index', $business)
            ->with('success', 'Link updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business, BusinessLink $link): RedirectResponse
    {
        $link->delete();

        // Reorder remaining links
        $business->links()->where('order', '>', $link->order)
            ->decrement('order');

        return redirect()->route('admin.businesses.links.index', $business)
            ->with('success', 'Link deleted successfully!');
    }

    /**
     * Reorder links.
     */
    public function reorder(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'links' => ['required', 'array'],
            'links.*.id' => ['required', 'exists:business_links,id'],
            'links.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['links'] as $linkData) {
            BusinessLink::where('id', $linkData['id'])
                ->where('business_id', $business->id)
                ->update(['order' => $linkData['order']]);
        }

        return redirect()->route('admin.businesses.links.index', $business)
            ->with('success', 'Links reordered successfully!');
    }
}
