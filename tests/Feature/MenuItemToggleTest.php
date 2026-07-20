<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MenuItemToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_owner_can_deactivate_and_reactivate_menu_item(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'is_active' => true,
        ]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Burger',
            'is_active' => true,
        ]);

        $deactivate = $this->actingAs($owner)->patch(route('business.menu.items.toggle-active', [
            $business->nanoid,
            $category->id,
            $item->id,
        ]));

        $deactivate->assertRedirect();
        $this->assertFalse($item->fresh()->is_active);

        $activate = $this->actingAs($owner)->patch(route('business.menu.items.toggle-active', [
            $business->nanoid,
            $category->id,
            $item->id,
        ]));

        $activate->assertRedirect();
        $this->assertTrue($item->fresh()->is_active);
    }

    public function test_admin_can_toggle_menu_item_active(): void
    {
        $admin = User::factory()->create();
        $business = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.businesses.items.toggle-active', [
            $business->nanoid,
            $category->id,
            $item->id,
        ]));

        $response->assertRedirect();
        $this->assertFalse($item->fresh()->is_active);
    }

    public function test_inactive_menu_items_are_hidden_on_public_menu(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Burgers',
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Available Burger',
            'is_active' => true,
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Unavailable Burger',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Public/BusinessMenu')
            ->has('categories', 1)
            ->has('categories.0.items', 1)
            ->where('categories.0.items.0.name', 'Available Burger')
        );
    }

    public function test_inactive_menu_items_are_hidden_on_public_landing_page(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Burgers',
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Available Burger',
            'is_active' => true,
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Unavailable Burger',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Public/BusinessLanding')
            ->has('categories.0.items', 1)
            ->where('categories.0.items.0.name', 'Available Burger')
        );
    }

    public function test_business_owner_cannot_toggle_item_from_another_business(): void
    {
        $owner = BusinessUser::factory()->create();
        Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $otherBusiness = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->patch(route('business.menu.items.toggle-active', [
            $otherBusiness->nanoid,
            $category->id,
            $item->id,
        ]));

        $response->assertNotFound();
        $this->assertTrue($item->fresh()->is_active);
    }
}
