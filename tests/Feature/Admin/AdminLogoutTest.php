<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post('/adminos/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
