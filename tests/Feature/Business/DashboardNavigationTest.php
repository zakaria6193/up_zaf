<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_dashboard_lists_owned_businesses_for_setup(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Burger & Co',
        ]);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Business/Dashboard')
                ->has('businesses', 1)
                ->where('businesses.0.nanoid', $business->nanoid)
                ->where('businesses.0.name', 'Burger & Co')
            );
    }

    public function test_shared_auth_includes_businesses_for_sidebar(): void
    {
        $owner = BusinessUser::factory()->create();
        $alpha = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Alpha Cafe',
        ]);
        $beta = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Beta Kitchen',
        ]);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Business/Dashboard')
                ->has('auth.user.businesses', 2)
                ->where('auth.user.businesses.0.nanoid', $alpha->nanoid)
                ->where('auth.user.businesses.0.name', 'Alpha Cafe')
                ->where('auth.user.businesses.1.nanoid', $beta->nanoid)
                ->where('auth.user.businesses.1.name', 'Beta Kitchen')
            );
    }

    public function test_account_settings_page_is_available(): void
    {
        $owner = BusinessUser::factory()->create();

        $this->actingAs($owner)
            ->get(route('business.settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Business/Settings')
                ->where('user.name', $owner->name)
                ->where('user.email', $owner->email)
            );
    }

    public function test_business_setup_pages_remain_scoped_by_business_query(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        foreach (['business.profile', 'business.menu', 'business.qr-code', 'business.links'] as $routeName) {
            $this->actingAs($owner)
                ->get(route($routeName, ['business' => $business->nanoid]))
                ->assertOk();
        }
    }
}
