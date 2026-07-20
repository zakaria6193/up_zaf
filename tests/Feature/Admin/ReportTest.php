<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create();
        Business::factory()->create([
            'created_at' => now()->subMonths(1),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports')
            ->has('overview')
            ->has('top_businesses')
            ->has('recent_businesses')
            ->has('businesses_by_month')
            ->has('top_users'));
    }
}
