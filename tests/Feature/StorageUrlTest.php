<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class StorageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_logo_and_item_images_use_host_relative_urls(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'logo' => 'logos/demo-logo.jpg',
        ]);

        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Burgers',
        ]);

        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Classic Burger',
            'image' => 'menu-items/burger.jpg',
            'is_active' => true,
        ]);

        $this->assertSame('/storage/logos/demo-logo.jpg', $business->logoUrl());

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Public/BusinessLanding')
            ->where('business.logo', '/storage/logos/demo-logo.jpg')
            ->where('categories.0.items.0.image', '/storage/menu-items/burger.jpg')
        );
    }
}
