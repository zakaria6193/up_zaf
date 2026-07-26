<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessQrTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_customize_qr_card_text(): void
    {
        Storage::fake('public');

        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'qr_style' => 'pulse',
            'qr_label' => null,
            'qr_headline' => null,
        ]);

        $response = $this->actingAs($owner)->patch(route('business.qr-code.text', $business->nanoid), [
            'qr_label' => 'Carte',
            'qr_headline' => 'Découvrez nos plats',
        ]);

        $response->assertRedirect();
        $business->refresh();

        $this->assertSame('Carte', $business->qr_label);
        $this->assertSame('Découvrez nos plats', $business->qr_headline);
        $this->assertSame('Carte', $business->qrLabelText());
        $this->assertSame('Découvrez nos plats', $business->qrHeadlineText());
        Storage::disk('public')->assertExists($business->qr_code);
    }

    public function test_empty_text_falls_back_to_style_defaults(): void
    {
        Storage::fake('public');

        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
            'qr_style' => 'noir',
            'qr_label' => 'Carte',
            'qr_headline' => 'Custom line',
        ]);

        $response = $this->actingAs($owner)->patch(route('business.qr-code.text', $business->nanoid), [
            'qr_label' => '',
            'qr_headline' => '',
        ]);

        $response->assertRedirect();
        $business->refresh();

        $this->assertNull($business->qr_label);
        $this->assertNull($business->qr_headline);
        $this->assertSame('MENU', $business->qrLabelText());
        $this->assertSame("See what's cooking", $business->qrHeadlineText());
    }

    public function test_qr_text_validation_rejects_overlong_values(): void
    {
        $owner = BusinessUser::factory()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->patch(route('business.qr-code.text', $business->nanoid), [
            'qr_label' => str_repeat('A', 25),
            'qr_headline' => str_repeat('B', 49),
        ]);

        $response->assertSessionHasErrors(['qr_label', 'qr_headline']);
    }
}
