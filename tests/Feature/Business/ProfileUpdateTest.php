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
}
