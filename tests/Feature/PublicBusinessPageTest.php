<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLink;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBusinessPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_business_shows_not_found(): void
    {
        $business = Business::factory()->create(['is_active' => false]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Public/NotFound'));
    }

    public function test_nonexistent_business_shows_not_found(): void
    {
        $response = $this->get(route('public.business.show', 'invalid-nanoid'));

        // Laravel returns 404 for invalid route model binding
        $response->assertStatus(404);
    }

    public function test_business_with_no_links_and_no_menu_shows_not_found(): void
    {
        $business = Business::factory()->create(['is_active' => true]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Public/NotFound'));
    }

    public function test_business_with_single_active_link_redirects_directly(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $link = BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'website',
            'url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertRedirect('https://example.com');
    }

    public function test_business_with_multiple_active_links_shows_landing_page(): void
    {
        $business = Business::factory()->create(['is_active' => true, 'name' => 'Test Restaurant']);
        BusinessLink::factory()->count(2)->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->has('business')
            ->where('business.name', 'Test Restaurant')
        );
    }

    public function test_business_with_menu_shows_landing_page(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Appetizers',
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Caesar Salad',
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->where('hasMenu', true)
            ->has('categories', 1)
            ->where('categories.0.name', 'Appetizers')
        );
    }

    public function test_landing_page_filters_out_menu_type_links(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        // Add a menu so it shows landing page instead of redirecting
        $category = MenuCategory::factory()->create(['business_id' => $business->id]);

        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'menu',
            'is_active' => true,
        ]);
        $instagramLink = BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'instagram',
            'label' => 'Instagram Link',
            'is_active' => true,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->has('links')
            ->where('hasMenu', true)
        );

        // Verify the instagram link exists in the response and menu link is filtered out
        $links = $response->viewData('page')['props']['links'];
        $this->assertCount(1, $links);
        $linkTypes = array_column($links, 'type');
        $this->assertContains('instagram', $linkTypes);
        $this->assertNotContains('menu', $linkTypes);
    }

    public function test_landing_page_only_shows_active_links(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        // Add multiple active links to force landing page display
        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'instagram',
            'label' => 'Active Link 1',
            'is_active' => true,
        ]);
        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'website',
            'label' => 'Active Link 2',
            'is_active' => true,
        ]);
        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'whatsapp',
            'label' => 'Inactive Link',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->has('links', 2)
        );
    }

    public function test_menu_page_shows_all_categories_and_items(): void
    {
        $business = Business::factory()->create(['is_active' => true, 'name' => 'Test Cafe']);
        $category1 = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Drinks',
        ]);
        $category2 = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Food',
        ]);
        MenuItem::factory()->create([
            'category_id' => $category1->id,
            'name' => 'Coffee',
            'price' => 5.00,
        ]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessMenu')
            ->where('business.name', 'Test Cafe')
            ->has('categories', 2)
        );
    }

    public function test_menu_page_for_inactive_business_shows_not_found(): void
    {
        $business = Business::factory()->create(['is_active' => false]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Public/NotFound'));
    }

    public function test_visiting_business_page_increments_views(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'total_views' => 10,
            'views_this_week' => 5,
        ]);
        BusinessLink::factory()->count(2)->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $this->get(route('public.business.show', $business->nanoid));

        $business->refresh();
        $this->assertEquals(11, $business->total_views);
        $this->assertEquals(6, $business->views_this_week);
    }

    public function test_visiting_menu_page_increments_views(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'total_views' => 20,
            'views_this_week' => 10,
        ]);
        MenuCategory::factory()->create(['business_id' => $business->id]);

        $this->get(route('public.business.menu', $business->nanoid));

        $business->refresh();
        $this->assertEquals(21, $business->total_views);
        $this->assertEquals(11, $business->views_this_week);
    }

    public function test_growth_percentage_is_calculated_on_visit(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'total_views' => 100,
            'views_this_week' => 20,
        ]);
        BusinessLink::factory()->count(2)->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $this->get(route('public.business.show', $business->nanoid));

        $business->refresh();
        $this->assertNotNull($business->growth_percentage);
    }

    public function test_subcategories_are_included_in_menu(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $parentCategory = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Main Menu',
            'parent_id' => null,
        ]);
        $subcategory = MenuCategory::factory()->create([
            'business_id' => $business->id,
            'name' => 'Specials',
            'parent_id' => $parentCategory->id,
        ]);
        MenuItem::factory()->create([
            'category_id' => $subcategory->id,
            'name' => 'Special Item',
        ]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessMenu')
            ->has('categories', 1)
            ->where('categories.0.name', 'Main Menu')
            ->has('categories.0.subcategories', 1)
            ->where('categories.0.subcategories.0.name', 'Specials')
        );
    }

    public function test_landing_page_includes_business_details(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'name' => 'Test Business',
            'address' => '123 Test St',
            'color' => '#FF5733',
        ]);
        BusinessLink::factory()->count(2)->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->where('business.name', 'Test Business')
            ->where('business.address', '123 Test St')
            ->where('business.color', '#FF5733')
        );
    }

    public function test_menu_items_include_prices_and_descriptions(): void
    {
        $business = Business::factory()->create(['is_active' => true]);
        $category = MenuCategory::factory()->create([
            'business_id' => $business->id,
        ]);
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Burger',
            'description' => 'Delicious burger',
            'price' => 12.50,
        ]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessMenu')
            ->where('categories.0.items.0.name', 'Burger')
            ->where('categories.0.items.0.description', 'Delicious burger')
            ->where('categories.0.items.0.price', '12.50')
        );
    }
}
