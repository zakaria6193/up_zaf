<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicBusinessController extends Controller
{
    /**
     * Show public business page with smart redirect logic.
     * - 0 active links: 404
     * - 1 active link: direct redirect
     * - 2+ active links: show landing page
     */
    public function show(Request $request, string $nanoid): Response|RedirectResponse
    {
        $business = Business::where('nanoid', $nanoid)
            ->where('is_active', true)
            ->with(['activeLinks', 'businessUser', 'menuCategories.items', 'menuCategories.subcategories.items'])
            ->first();

        // Business not found or inactive
        if (! $business) {
            return Inertia::render('Public/NotFound');
        }

        if (! $this->ownerPublicAccessAllowed($business)) {
            return Inertia::render('Public/Unavailable', [
                'businessName' => $business->name,
            ]);
        }

        // Track visit
        $this->trackVisit($business, $request);

        $activeLinksCount = $business->activeLinks->count();
        $parentCategories = $business->menuCategories
            ->filter(fn ($cat) => is_null($cat->parent_id))
            ->values();
        $hasMenu = $parentCategories->count() > 0;

        // No active links and no menu - show not found
        if ($activeLinksCount === 0 && ! $hasMenu) {
            return Inertia::render('Public/NotFound');
        }

        // Single active link that's NOT menu - direct redirect
        if ($activeLinksCount === 1 && ! $hasMenu) {
            $link = $business->activeLinks->first();
            if ($link->type !== 'menu') {
                return redirect()->away($link->url);
            }
        }

        // Show landing page with menu inline
        return Inertia::render('Public/BusinessLanding', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'logo' => $business->logoUrl(),
                'color' => $business->color,
                'currency' => $business->currencyCode(),
                'seo_title' => $business->seo_title,
                'seo_description' => $business->seo_description,
                'seo_keywords' => $business->seo_keywords,
                'lat' => $business->lat,
                'lng' => $business->lng,
            ],
            'links' => $business->activeLinks->filter(fn ($link) => $link->type !== 'menu')->values()->map(fn ($link) => [
                'id' => $link->id,
                'type' => $link->type,
                'label' => $link->label,
                'url' => $link->url,
            ])->values(),
            'hasMenu' => $hasMenu,
            'categories' => $parentCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'order' => $category->order,
                'items' => $category->items->where('is_active', true)->values()->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->imageUrl(),
                ])->values(),
                'subcategories' => $category->subcategories->map(fn ($subcategory) => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'items' => $subcategory->items->where('is_active', true)->values()->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'image' => $item->imageUrl(),
                    ])->values(),
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Show public menu page.
     */
    public function menu(Request $request, string $nanoid): Response
    {
        $business = Business::where('nanoid', $nanoid)
            ->where('is_active', true)
            ->with(['menuCategories.items', 'menuCategories.subcategories.items', 'businessUser'])
            ->first();

        // Business not found or inactive
        if (! $business) {
            return Inertia::render('Public/NotFound');
        }

        if (! $this->ownerPublicAccessAllowed($business)) {
            return Inertia::render('Public/Unavailable', [
                'businessName' => $business->name,
            ]);
        }

        // Track visit
        $this->trackVisit($business, $request);

        // Only get parent categories (those without parent_id), reindexed so JSON stays an array
        $parentCategories = $business->menuCategories
            ->filter(fn ($cat) => is_null($cat->parent_id))
            ->values();

        return Inertia::render('Public/BusinessMenu', [
            'business' => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'address' => $business->address,
                'logo' => $business->logoUrl(),
                'color' => $business->color,
                'currency' => $business->currencyCode(),
                'seo_title' => $business->seo_title,
                'seo_description' => $business->seo_description,
                'lat' => $business->lat,
                'lng' => $business->lng,
            ],
            'categories' => $parentCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'items' => $category->items->where('is_active', true)->values()->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->imageUrl(),
                ])->values(),
                'subcategories' => $category->subcategories->map(fn ($subcategory) => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'items' => $subcategory->items->where('is_active', true)->values()->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'image' => $item->imageUrl(),
                    ])->values(),
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Free accounts keep dashboard access, but public pages lock after the trial ends.
     */
    protected function ownerPublicAccessAllowed(Business $business): bool
    {
        $owner = $business->businessUser;

        // Orphan / admin-only businesses stay publicly reachable.
        if (! $owner) {
            return true;
        }

        return $owner->publicPagesAccessible();
    }

    /**
     * Track visit for analytics.
     */
    protected function trackVisit(Business $business, Request $request): void
    {
        // Increment total views
        $business->increment('total_views');

        // Increment weekly views
        $business->increment('views_this_week');

        // Calculate growth percentage
        $previousWeekViews = $business->total_views - $business->views_this_week;
        if ($previousWeekViews > 0) {
            $growth = (($business->views_this_week - $previousWeekViews) / $previousWeekViews) * 100;
            $business->update(['growth_percentage' => round($growth, 2)]);
        }
    }
}
