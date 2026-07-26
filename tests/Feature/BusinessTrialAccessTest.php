<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLink;
use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTrialAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_works_during_trial(): void
    {
        $owner = BusinessUser::factory()->create([
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(10),
        ]);

        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'is_active' => true,
        ]);

        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'type' => 'website',
            'url' => 'https://example.com',
            'is_active' => true,
        ]);

        $this->get(route('public.business.show', $business->nanoid))
            ->assertRedirect('https://example.com');
    }

    public function test_public_page_is_blocked_after_trial_expires(): void
    {
        $owner = BusinessUser::factory()->trialExpired()->create();

        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'is_active' => true,
            'name' => 'Locked Bistro',
        ]);

        BusinessLink::factory()->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $this->get(route('public.business.show', $business->nanoid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Unavailable')
                ->where('businessName', 'Locked Bistro')
            );
    }

    public function test_public_menu_is_blocked_after_trial_expires(): void
    {
        $owner = BusinessUser::factory()->trialExpired()->create();

        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'is_active' => true,
        ]);

        $this->get(route('public.business.menu', $business->nanoid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Public/Unavailable'));
    }

    public function test_premium_owners_keep_public_access_without_trial(): void
    {
        $owner = BusinessUser::factory()->premium()->create();

        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'is_active' => true,
        ]);

        BusinessLink::factory()->count(2)->create([
            'business_id' => $business->id,
            'is_active' => true,
        ]);

        $this->get(route('public.business.show', $business->nanoid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Public/BusinessLanding'));
    }
}
