<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\BusinessLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessLinkCrudTest extends TestCase
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

    public function test_admin_can_view_business_links_page(): void
    {
        $link1 = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'label' => 'Follow us on Instagram',
        ]);
        $link2 = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'label' => 'Leave a review',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.businesses.links.index', $this->business->nanoid));

        $response->assertStatus(200);
        $response->assertSee('Follow us on Instagram');
        $response->assertSee('Leave a review');
    }

    public function test_admin_can_create_business_link(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.store', $this->business->nanoid), [
                'type' => 'instagram',
                'label' => 'Follow us on Instagram',
                'url' => 'https://instagram.com/testbusiness',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('business_links', [
            'business_id' => $this->business->id,
            'type' => 'instagram',
            'label' => 'Follow us on Instagram',
            'url' => 'https://instagram.com/testbusiness',
        ]);
    }

    public function test_link_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.store', $this->business->nanoid), []);

        $response->assertSessionHasErrors(['type', 'label', 'url']);
    }

    public function test_link_creation_validates_url_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.store', $this->business->nanoid), [
                'type' => 'website',
                'label' => 'Our Website',
                'url' => 'not-a-valid-url',
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors(['url']);
    }

    public function test_cannot_create_more_than_4_active_links(): void
    {
        // Create 4 active links
        BusinessLink::factory()->count(4)->create([
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.store', $this->business->nanoid), [
                'type' => 'website',
                'label' => 'Fifth Link',
                'url' => 'https://example.com',
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors(['is_active']);
    }

    public function test_can_create_inactive_link_when_4_active_links_exist(): void
    {
        // Create 4 active links
        BusinessLink::factory()->count(4)->create([
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.store', $this->business->nanoid), [
                'type' => 'website',
                'label' => 'Fifth Link (Inactive)',
                'url' => 'https://example.com',
                'is_active' => false,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('business_links', [
            'business_id' => $this->business->id,
            'label' => 'Fifth Link (Inactive)',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_update_business_link(): void
    {
        $link = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'label' => 'Old Label',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.businesses.links.update', [$this->business->nanoid, $link->id]), [
                'type' => $link->type,
                'label' => 'New Label',
                'url' => $link->url,
                'is_active' => $link->is_active,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('business_links', [
            'id' => $link->id,
            'label' => 'New Label',
        ]);
    }

    public function test_cannot_activate_link_when_4_active_links_exist(): void
    {
        // Create 4 active links
        BusinessLink::factory()->count(4)->create([
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        // Create an inactive link
        $inactiveLink = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.businesses.links.update', [$this->business->nanoid, $inactiveLink->id]), [
                'type' => $inactiveLink->type,
                'label' => $inactiveLink->label,
                'url' => $inactiveLink->url,
                'is_active' => true,
            ]);

        $response->assertSessionHasErrors(['is_active']);
    }

    public function test_admin_can_delete_business_link(): void
    {
        $link = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.businesses.links.destroy', [$this->business->nanoid, $link->id]));

        $response->assertRedirect();

        $this->assertDatabaseMissing('business_links', [
            'id' => $link->id,
        ]);
    }

    public function test_admin_can_reorder_business_links(): void
    {
        $link1 = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'order' => 0,
        ]);
        $link2 = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'order' => 1,
        ]);
        $link3 = BusinessLink::factory()->create([
            'business_id' => $this->business->id,
            'order' => 2,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.businesses.links.reorder', $this->business->nanoid), [
                'links' => [
                    ['id' => $link3->id, 'order' => 0],
                    ['id' => $link1->id, 'order' => 1],
                    ['id' => $link2->id, 'order' => 2],
                ],
            ]);

        $response->assertRedirect();

        $link1->refresh();
        $link2->refresh();
        $link3->refresh();

        $this->assertEquals(1, $link1->order);
        $this->assertEquals(2, $link2->order);
        $this->assertEquals(0, $link3->order);
    }

    public function test_different_link_types_can_be_created(): void
    {
        $types = ['google_reviews', 'google_maps', 'menu', 'instagram', 'whatsapp', 'website', 'other'];

        foreach ($types as $index => $type) {
            BusinessLink::factory()->create([
                'business_id' => $this->business->id,
                'type' => $type,
                'is_active' => $index < 4, // Only first 4 active
            ]);
        }

        $this->assertEquals(7, $this->business->links()->count());
        $this->assertEquals(4, $this->business->activeLinks()->count());
    }

    public function test_links_belong_to_correct_business(): void
    {
        $business2 = Business::factory()->create();

        $link1 = BusinessLink::factory()->create(['business_id' => $this->business->id]);
        $link2 = BusinessLink::factory()->create(['business_id' => $business2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.businesses.links.index', $this->business->nanoid));

        $response->assertSee($link1->label);
        $response->assertDontSee($link2->label);
    }

    public function test_guest_cannot_access_business_links_crud(): void
    {
        $link = BusinessLink::factory()->create(['business_id' => $this->business->id]);

        $this->get(route('admin.businesses.links.index', $this->business->nanoid))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.businesses.links.store', $this->business->nanoid), [])
            ->assertRedirect(route('admin.login'));

        $this->put(route('admin.businesses.links.update', [$this->business->nanoid, $link->id]), [])
            ->assertRedirect(route('admin.login'));

        $this->delete(route('admin.businesses.links.destroy', [$this->business->nanoid, $link->id]))
            ->assertRedirect(route('admin.login'));
    }
}
