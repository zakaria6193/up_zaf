<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_options_include_common_codes(): void
    {
        $codes = Currency::codes();

        foreach (['MAD', 'USD', 'EUR', 'GBP', 'AED', 'SAR'] as $code) {
            $this->assertContains($code, $codes);
        }
    }

    public function test_admin_create_page_includes_currency_options(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.businesses.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Businesses/Create')
            ->has('currencies')
            ->where('currencies.0.code', 'MAD')
        );
    }

    public function test_admin_can_create_business_with_usd(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.businesses.store'), [
            'name' => 'Dollar Cafe',
            'color' => '#3b82f6',
            'currency' => 'USD',
            'qr_style' => 'pulse',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'name' => 'Dollar Cafe',
            'currency' => 'USD',
        ]);
    }

    public function test_admin_cannot_create_business_with_invalid_currency(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.businesses.store'), [
            'name' => 'Bad Currency Biz',
            'color' => '#3b82f6',
            'currency' => 'XXX',
            'qr_style' => 'pulse',
        ]);

        $response->assertSessionHasErrors(['currency']);
        $this->assertDatabaseMissing('businesses', [
            'name' => 'Bad Currency Biz',
        ]);
    }

    public function test_public_page_exposes_business_currency(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'currency' => 'EUR',
        ]);
        $category = MenuCategory::factory()->create(['business_id' => $business->id]);
        MenuItem::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('public.business.show', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessLanding')
            ->where('business.currency', 'EUR')
        );
    }

    public function test_public_menu_exposes_business_currency(): void
    {
        $business = Business::factory()->create([
            'is_active' => true,
            'currency' => 'GBP',
        ]);
        $category = MenuCategory::factory()->create(['business_id' => $business->id]);
        MenuItem::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('public.business.menu', $business->nanoid));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/BusinessMenu')
            ->where('business.currency', 'GBP')
        );
    }

    public function test_business_profile_page_includes_currencies_list(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'currency' => 'CAD',
        ]);

        $response = $this->actingAs($owner)->get(route('business.profile', ['business' => $business->nanoid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Profile')
            ->where('business.currency', 'CAD')
            ->has('currencies')
        );
    }

    public function test_business_owner_can_update_currency_from_menu_page(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'currency' => 'MAD',
        ]);

        $response = $this->actingAs($owner)->patch(route('business.menu.currency.update', $business->nanoid), [
            'currency' => 'USD',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'currency' => 'USD',
        ]);
    }

    public function test_admin_can_update_currency_from_menu_page(): void
    {
        $admin = User::factory()->create();
        $business = Business::factory()->create(['currency' => 'MAD']);

        $response = $this->actingAs($admin)->patch(route('admin.businesses.menu.currency.update', $business->nanoid), [
            'currency' => 'EUR',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'currency' => 'EUR',
        ]);
    }

    public function test_business_menu_page_includes_currency_options(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'currency' => 'GBP',
        ]);

        $response = $this->actingAs($owner)->get(route('business.menu', ['business' => $business->nanoid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Menu')
            ->where('business.currency', 'GBP')
            ->has('currencies')
        );
    }
}
