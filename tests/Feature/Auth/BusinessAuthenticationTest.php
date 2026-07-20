<?php

namespace Tests\Feature\Auth;

use App\Auth\DualEloquentUserProvider;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_users_can_authenticate_with_email(): void
    {
        $businessUser = BusinessUser::factory()->create([
            'email' => 'mohamed@example.com',
            'phone' => '+212600111111',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'mohamed@example.com',
            'password' => 'password',
            'login_type' => 'business',
        ]);

        $response->assertRedirect(route('business.dashboard', absolute: false));
        $this->assertAuthenticatedAs($businessUser);
        $this->assertSame(BusinessUser::class, session(DualEloquentUserProvider::SESSION_KEY));
    }

    public function test_business_users_can_authenticate_with_phone(): void
    {
        $businessUser = BusinessUser::factory()->create([
            'email' => 'fatima@example.com',
            'phone' => '+212600222222',
        ]);

        $response = $this->post(route('login.store'), [
            'phone' => '+212600222222',
            'password' => 'password',
            'login_type' => 'business',
        ]);

        $response->assertRedirect(route('business.dashboard', absolute: false));
        $this->assertAuthenticatedAs($businessUser);
    }

    public function test_business_session_survives_follow_up_request_with_colliding_admin_id(): void
    {
        $admin = User::factory()->create();
        $businessUser = BusinessUser::factory()->create([
            'email' => 'owner@example.com',
            'phone' => '+212600333333',
        ]);

        $this->assertSame($admin->id, $businessUser->id);

        $this->post(route('login.store'), [
            'email' => 'owner@example.com',
            'password' => 'password',
            'login_type' => 'business',
        ])->assertRedirect(route('business.dashboard', absolute: false));

        $this->get(route('business.dashboard'))
            ->assertOk();

        $this->assertAuthenticatedAs($businessUser);
        $this->assertInstanceOf(BusinessUser::class, auth()->user());
    }

    public function test_business_users_cannot_authenticate_with_invalid_password(): void
    {
        BusinessUser::factory()->create([
            'email' => 'mohamed@example.com',
        ]);

        $this->post(route('login.store'), [
            'email' => 'mohamed@example.com',
            'password' => 'wrong-password',
            'login_type' => 'business',
        ]);

        $this->assertGuest();
    }

    public function test_admin_users_can_still_authenticate(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
            'login_type' => 'admin',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
        $this->assertSame(User::class, session(DualEloquentUserProvider::SESSION_KEY));
    }
}
