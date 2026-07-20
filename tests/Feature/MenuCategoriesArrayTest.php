<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MenuCategoriesArrayTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_menu_categories_are_always_a_json_array_when_subcategories_exist(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $parent = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Drinks',
            'parent_id' => null,
            'order' => 0,
        ]);

        MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Hot Drinks',
            'parent_id' => $parent->id,
            'order' => 0,
        ]);

        MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Food',
            'parent_id' => null,
            'order' => 1,
        ]);

        $response = $this->actingAs($owner)
            ->get('/business/menu?business='.$business->nanoid);

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Business/Menu')
            ->has('categories', 2)
            ->where('categories', function ($categories) {
                $array = $categories instanceof Collection
                    ? $categories->all()
                    : $categories;

                return is_array($array) && array_is_list($array);
            })
            ->where('categories.0.name', 'Drinks')
            ->where('categories.0.subcategories.0.name', 'Hot Drinks')
            ->where('categories.1.name', 'Food')
        );
    }

    public function test_admin_menu_categories_are_always_a_json_array_when_subcategories_exist(): void
    {
        $admin = User::factory()->create();
        $business = Business::factory()->create();

        $parent = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Drinks',
            'parent_id' => null,
            'order' => 0,
        ]);

        MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Hot Drinks',
            'parent_id' => $parent->id,
            'order' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.businesses.menu.index', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Businesses/Menu/Index')
            ->has('categories', 1)
            ->where('categories', function ($categories) {
                $array = $categories instanceof Collection
                    ? $categories->all()
                    : $categories;

                return is_array($array) && array_is_list($array);
            })
        );
    }
}
