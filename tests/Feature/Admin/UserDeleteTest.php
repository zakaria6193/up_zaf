<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_user_with_businesses_and_sees_error_flash(): void
    {
        $admin = User::factory()->create();
        $owner = BusinessUser::factory()->create();
        Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $owner));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('business_users', [
            'id' => $owner->id,
        ]);
    }

    public function test_admin_can_delete_user_without_businesses(): void
    {
        $admin = User::factory()->create();
        $owner = BusinessUser::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $owner));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('business_users', [
            'id' => $owner->id,
        ]);
    }
}
