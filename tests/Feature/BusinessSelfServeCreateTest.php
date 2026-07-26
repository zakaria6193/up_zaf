<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessSelfServeCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_can_create_one_business(): void
    {
        $user = BusinessUser::factory()->create([
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('business.businesses.store'), [
            'name' => 'My Café',
            'color' => '#e4572e',
            'currency' => 'MAD',
            'qr_style' => 'pulse',
        ]);

        $business = Business::where('name', 'My Café')->first();

        $this->assertNotNull($business);
        $this->assertSame($user->id, $business->business_user_id);
        $response->assertRedirect(route('business.profile', ['business' => $business->nanoid]));
    }

    public function test_free_user_cannot_create_a_second_business(): void
    {
        $user = BusinessUser::factory()->create([
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(10),
        ]);

        Business::factory()->create(['business_user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('business.businesses.store'), [
                'name' => 'Second Spot',
                'color' => '#e4572e',
                'currency' => 'MAD',
                'qr_style' => 'pulse',
            ])
            ->assertRedirect(route('business.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('businesses', ['name' => 'Second Spot']);
    }

    public function test_premium_user_can_create_multiple_businesses(): void
    {
        $user = BusinessUser::factory()->premium()->create();

        Business::factory()->create(['business_user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('business.businesses.store'), [
                'name' => 'Premium Spot',
                'color' => '#0f766e',
                'currency' => 'MAD',
                'qr_style' => 'pulse',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('businesses', [
            'name' => 'Premium Spot',
            'business_user_id' => $user->id,
        ]);
    }

    public function test_create_form_is_blocked_when_free_limit_reached(): void
    {
        $user = BusinessUser::factory()->create([
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(10),
        ]);

        Business::factory()->create(['business_user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('business.businesses.create'))
            ->assertRedirect(route('business.dashboard'));
    }
}
