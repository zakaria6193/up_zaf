<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display business owner dashboard.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get all businesses owned by this user
        $businesses = $user->businesses()
            ->withCount(['activeLinks', 'menuCategories'])
            ->get();

        // Calculate total statistics
        $totalViews = $businesses->sum('total_views');
        $totalWeeklyViews = $businesses->sum('views_this_week');
        $avgGrowth = $businesses->avg('growth_percentage') ?? 0;

        return Inertia::render('Business/Dashboard', [
            'businesses' => $businesses->map(fn ($business) => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'is_active' => $business->is_active,
                'total_views' => $business->total_views,
                'views_this_week' => $business->views_this_week,
                'growth_percentage' => $business->growth_percentage,
                'active_links_count' => $business->active_links_count,
                'menu_categories_count' => $business->menu_categories_count,
                'logo' => $business->logoUrl(),
                'public_url' => $business->publicUrl(),
            ]),
            'stats' => [
                'total_views' => $totalViews,
                'weekly_views' => $totalWeeklyViews,
                'avg_growth' => round($avgGrowth, 2),
                'total_businesses' => $businesses->count(),
                'active_businesses' => $businesses->where('is_active', true)->count(),
            ],
        ]);
    }
}
