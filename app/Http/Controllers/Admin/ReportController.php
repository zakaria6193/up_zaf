<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        // Overview Statistics
        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();
        $totalUsers = BusinessUser::count();
        $orphanBusinesses = Business::whereNull('business_user_id')->count();

        // Total views across all businesses
        $totalViews = Business::sum('total_views');
        $weeklyViews = Business::sum('views_this_week');

        // Average growth
        $avgGrowth = Business::avg('growth_percentage') ?? 0;

        // Top performing businesses (by views this week)
        $topBusinesses = Business::query()
            ->orderBy('views_this_week', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($business) => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'views_this_week' => $business->views_this_week,
                'total_views' => $business->total_views,
                'growth_percentage' => (float) ($business->growth_percentage ?? 0),
                'is_active' => $business->is_active,
            ]);

        // Recent businesses (last 7 days)
        $recentBusinesses = Business::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($business) => [
                'nanoid' => $business->nanoid,
                'name' => $business->name,
                'created_at' => $business->created_at->format('d/m/Y H:i'),
                'is_active' => $business->is_active,
            ]);

        // Businesses by creation month (last 6 months)
        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $businessesByMonth = Business::query()
            ->select(
                DB::raw("{$monthExpression} as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($item) => [
                'month' => $item->month,
                'count' => $item->count,
            ]);

        // Users with most businesses
        $topUsers = BusinessUser::query()
            ->withCount('businesses')
            ->whereHas('businesses')
            ->orderBy('businesses_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'businesses_count' => $user->businesses_count,
            ]);

        return Inertia::render('Admin/Reports', [
            'overview' => [
                'total_businesses' => $totalBusinesses,
                'active_businesses' => $activeBusinesses,
                'total_users' => $totalUsers,
                'orphan_businesses' => $orphanBusinesses,
                'total_views' => $totalViews,
                'weekly_views' => $weeklyViews,
                'avg_growth' => round($avgGrowth, 1),
            ],
            'top_businesses' => $topBusinesses,
            'recent_businesses' => $recentBusinesses,
            'businesses_by_month' => $businessesByMonth,
            'top_users' => $topUsers,
        ]);
    }
}
