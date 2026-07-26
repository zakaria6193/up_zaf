<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuItemUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_owner_can_add_description_when_updating_item(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Burger',
            'description' => null,
            'price' => 45.00,
        ]);

        $response = $this->actingAs($owner)->post(route('business.menu.items.update', [
            $business->nanoid,
            $category->id,
            $item->id,
        ]), [
            'name' => 'Burger',
            'description' => 'Beef patty with cheese',
            'price' => 45.00,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'name' => 'Burger',
            'description' => 'Beef patty with cheese',
            'price' => 45.00,
        ]);
    }

    public function test_business_owner_can_update_existing_item_description(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);
        $item = MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Burger',
            'description' => 'Old description',
            'price' => 45.00,
        ]);

        $response = $this->actingAs($owner)->post(route('business.menu.items.update', [
            $business->nanoid,
            $category->id,
            $item->id,
        ]), [
            'name' => 'Cheese Burger',
            'description' => 'Updated description',
            'price' => 50.00,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'name' => 'Cheese Burger',
            'description' => 'Updated description',
            'price' => 50.00,
        ]);
    }

    public function test_business_owner_can_create_item_with_photo(): void
    {
        Storage::fake('public');

        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);

        $response = $this->actingAs($owner)->post(route('business.menu.items.store', [
            $business->nanoid,
            $category->id,
        ]), [
            'name' => 'Photo Burger',
            'description' => 'With fries',
            'price' => 55.00,
            'image' => UploadedFile::fake()->image('burger.jpg'),
        ]);

        $response->assertRedirect();

        $item = MenuItem::query()->where('name', 'Photo Burger')->first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);
    }
}
