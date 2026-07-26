<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_owner_can_update_profile(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($owner)->put(route('business.profile.update', $business->nanoid), [
            'name' => 'New Name',
            'address' => 'Casablanca',
            'lat' => 33.57,
            'lng' => -7.58,
            'color' => '#112233',
            'currency' => 'EUR',
            'qr_style' => 'pulse',
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
            'seo_keywords' => 'a, b',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'New Name',
            'address' => 'Casablanca',
            'color' => '#112233',
            'currency' => 'EUR',
            'qr_style' => 'pulse',
        ]);
    }

    public function test_business_owner_cannot_set_invalid_currency(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'currency' => 'MAD',
        ]);

        $response = $this->actingAs($owner)->from(route('business.profile'))->put(route('business.profile.update', $business->nanoid), [
            'name' => $business->name,
            'color' => $business->color,
            'currency' => 'XYZ',
            'qr_style' => 'pulse',
        ]);

        $response->assertSessionHasErrors(['currency']);
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'currency' => 'MAD',
        ]);
    }

    public function test_business_owner_can_switch_business_via_query_param(): void
    {
        $owner = BusinessUser::factory()->create();
        $first = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'First Biz',
        ]);
        $second = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Second Biz',
        ]);

        $response = $this->actingAs($owner)->get('/business/profile?business='.$second->nanoid);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Profile')
            ->where('business.nanoid', $second->nanoid)
            ->where('business.name', 'Second Biz')
            ->where('business.nanoid', fn ($nanoid) => $nanoid !== $first->nanoid)
        );
    }

    public function test_manage_from_dashboard_opens_selected_business_not_first(): void
    {
        $owner = BusinessUser::factory()->create();
        $first = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Restaurant One',
        ]);
        $third = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Restaurant Three',
        ]);

        // Simulate clicking Manage on the 3rd card: profile?business=<third>
        $response = $this->actingAs($owner)->get(route('business.profile', [
            'business' => $third->nanoid,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Profile')
            ->where('business.nanoid', $third->nanoid)
            ->where('business.name', 'Restaurant Three')
            ->where('business.nanoid', fn ($nanoid) => $nanoid !== $first->nanoid)
        );
    }

    public function test_menu_page_respects_selected_business_query(): void
    {
        $owner = BusinessUser::factory()->create();
        Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'First',
        ]);
        $second = Business::factory()->create([
            'business_user_id' => $owner->id,
            'name' => 'Second',
        ]);

        $response = $this->actingAs($owner)->get(route('business.menu', [
            'business' => $second->nanoid,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Menu')
            ->where('business.nanoid', $second->nanoid)
            ->where('business.name', 'Second')
        );
    }
}
