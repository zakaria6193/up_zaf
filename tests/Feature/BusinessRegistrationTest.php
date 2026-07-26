<?php

namespace Tests\Feature;

use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_displayed(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Business/Register'));
    }

    public function test_new_users_can_register_with_a_trial(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Owner',
            'email' => 'owner@example.com',
            'phone' => '+212600999999',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('business.dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = BusinessUser::where('email', 'owner@example.com')->first();

        $this->assertNotNull($user);
        $this->assertFalse($user->is_premium);
        $this->assertTrue($user->isOnTrial());
        $this->assertTrue($user->publicPagesAccessible());
        $this->assertTrue(
            $user->trial_ends_at->between(
                now()->addMinutes(9),
                now()->addMinutes(11),
            ),
        );
    }

    public function test_landing_page_is_shown_to_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Marketing/Landing')
                ->has('trialMinutes')
            );
    }

    public function test_authenticated_business_users_are_redirected_from_home(): void
    {
        $user = BusinessUser::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('business.dashboard'));
    }
}
