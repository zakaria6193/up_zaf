<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->business = Business::factory()->create();
    }

    public function test_admin_can_view_menu_management_page(): void
    {
        $category1 = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Appetizers',
        ]);
        $category2 = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Main Courses',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.businesses.menu.index', $this->business->nanoid));

        $response->assertStatus(200);
        $response->assertSee('Appetizers');
        $response->assertSee('Main Courses');
    }

    public function test_admin_can_create_menu_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.categories.store', $this->business->nanoid), [
                'name' => 'Desserts',
                'parent_id' => null,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_categories', [
            'business_id' => $this->business->id,
            'name' => 'Desserts',
        ]);
    }

    public function test_category_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.categories.store', $this->business->nanoid), []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_update_menu_category(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.businesses.categories.update', [$this->business->nanoid, $category->id]), [
                'name' => 'New Name',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_menu_category(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.businesses.categories.destroy', [$this->business->nanoid, $category->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('menu_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_reorder_menu_categories(): void
    {
        $cat1 = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'order' => 0,
        ]);
        $cat2 = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'order' => 1,
        ]);
        $cat3 = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
            'order' => 2,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.categories.reorder', $this->business->nanoid), [
                'categories' => [
                    ['id' => $cat3->id, 'order' => 0],
                    ['id' => $cat1->id, 'order' => 1],
                    ['id' => $cat2->id, 'order' => 2],
                ],
            ]);

        $response->assertRedirect();

        $cat1->refresh();
        $cat2->refresh();
        $cat3->refresh();

        $this->assertEquals(1, $cat1->order);
        $this->assertEquals(2, $cat2->order);
        $this->assertEquals(0, $cat3->order);
    }

    public function test_admin_can_create_menu_item(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.items.store', [$this->business->nanoid, $category->id]), [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with Caesar dressing',
                'price' => 12.99,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'category_id' => $category->id,
            'name' => 'Caesar Salad',
            'price' => 12.99,
        ]);
    }

    public function test_item_creation_validates_required_fields(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.items.store', [$this->business->nanoid, $category->id]), []);

        $response->assertSessionHasErrors(['name', 'price']);
    }

    public function test_item_price_must_be_numeric(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.items.store', [$this->business->nanoid, $category->id]), [
                'name' => 'Test Item',
                'price' => 'not-a-number',
            ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function test_admin_can_update_menu_item(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Old Name',
            'price' => 10.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.businesses.items.update', [$this->business->nanoid, $category->id, $item->id]), [
                'name' => 'New Name',
                'description' => $item->description,
                'price' => 15.00,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'name' => 'New Name',
            'price' => 15.00,
        ]);
    }

    public function test_admin_can_delete_menu_item(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.businesses.items.destroy', [$this->business->nanoid, $category->id, $item->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('menu_items', [
            'id' => $item->id,
        ]);
    }

    public function test_admin_can_reorder_menu_items(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $item1 = MenuItem::factory()->create([
            'category_id' => $category->id,
            'order' => 0,
        ]);
        $item2 = MenuItem::factory()->create([
            'category_id' => $category->id,
            'order' => 1,
        ]);
        $item3 = MenuItem::factory()->create([
            'category_id' => $category->id,
            'order' => 2,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.items.reorder', [$this->business->nanoid, $category->id]), [
                'items' => [
                    ['id' => $item3->id, 'order' => 0],
                    ['id' => $item1->id, 'order' => 1],
                    ['id' => $item2->id, 'order' => 2],
                ],
            ]);

        $response->assertRedirect();

        $item1->refresh();
        $item2->refresh();
        $item3->refresh();

        $this->assertEquals(1, $item1->order);
        $this->assertEquals(2, $item2->order);
        $this->assertEquals(0, $item3->order);
    }

    public function test_deleting_category_deletes_its_items(): void
    {
        $category = MenuCategory::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $item1 = MenuItem::factory()->create(['category_id' => $category->id]);
        $item2 = MenuItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.businesses.categories.destroy', [$this->business->nanoid, $category->id]));

        $this->assertDatabaseMissing('menu_items', ['id' => $item1->id]);
        $this->assertDatabaseMissing('menu_items', ['id' => $item2->id]);
    }

    public function test_menu_categories_belong_to_correct_business(): void
    {
        $business2 = Business::factory()->create();

        $cat1 = MenuCategory::factory()->create(['business_id' => $this->business->id, 'name' => 'Business 1 Category']);
        $cat2 = MenuCategory::factory()->create(['business_id' => $business2->id, 'name' => 'Business 2 Category']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.businesses.menu.index', $this->business->nanoid));

        $response->assertSee('Business 1 Category');
        $response->assertDontSee('Business 2 Category');
    }

    public function test_guest_cannot_access_menu_crud(): void
    {
        $category = MenuCategory::factory()->create(['business_id' => $this->business->id]);
        $item = MenuItem::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.businesses.menu.index', $this->business->nanoid))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.businesses.categories.store', $this->business->nanoid), [])
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.businesses.items.store', [$this->business->nanoid, $category->id]), [])
            ->assertRedirect(route('admin.login'));

        $this->delete(route('admin.businesses.categories.destroy', [$this->business->nanoid, $category->id]))
            ->assertRedirect(route('admin.login'));

        $this->delete(route('admin.businesses.items.destroy', [$this->business->nanoid, $category->id, $item->id]))
            ->assertRedirect(route('admin.login'));
    }
}
