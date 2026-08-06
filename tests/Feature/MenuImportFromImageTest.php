<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\MenuCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuImportFromImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_users_cannot_parse_menu_images(): void
    {
        Storage::fake('public');
        config(['services.gemini.api_key' => 'test-key']);

        $owner = BusinessUser::factory()->create([
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes(10),
        ]);
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('business.menu.import.parse', $business->nanoid), [
                'image' => UploadedFile::fake()->image('menu.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_premium_users_can_parse_and_confirm_menu_import(): void
    {
        Storage::fake('public');
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-flash-latest',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'categories' => [
                                    [
                                        'name' => 'Burgers',
                                        'items' => [
                                            [
                                                'name' => 'Classic Smash',
                                                'description' => 'Cheddar and pickles',
                                                'price' => 65,
                                            ],
                                        ],
                                    ],
                                ],
                            ]),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $owner = BusinessUser::factory()->premium()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('business.menu.import.parse', $business->nanoid), [
                'image' => UploadedFile::fake()->image('menu.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHas('menu_import_draft');

        $this->actingAs($owner)
            ->post(route('business.menu.import.confirm', $business->nanoid), [
                'categories' => [
                    [
                        'name' => 'Burgers',
                        'items' => [
                            [
                                'name' => 'Classic Smash',
                                'description' => 'Cheddar and pickles',
                                'price' => 65,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('business.menu', ['business' => $business->nanoid]));

        $this->assertDatabaseHas('menu_categories', [
            'business_id' => $business->id,
            'name' => 'Burgers',
        ]);

        $category = MenuCategory::query()
            ->where('business_id', $business->id)
            ->where('name', 'Burgers')
            ->first();

        $this->assertNotNull($category);
        $this->assertDatabaseHas('menu_items', [
            'category_id' => $category->id,
            'name' => 'Classic Smash',
            'price' => 65,
        ]);
    }

    public function test_parse_requires_gemini_configuration(): void
    {
        config(['services.gemini.api_key' => null]);

        $owner = BusinessUser::factory()->premium()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('business.menu.import.parse', $business->nanoid), [
                'image' => UploadedFile::fake()->image('menu.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_gemini_quota_exceeded_returns_friendly_error(): void
    {
        Storage::fake('public');
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-flash-latest',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Resource exhausted',
                    'status' => 'RESOURCE_EXHAUSTED',
                ],
            ], 429),
        ]);

        $owner = BusinessUser::factory()->premium()->create();
        $business = Business::factory()->create([
            'business_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->post(route('business.menu.import.parse', $business->nanoid), [
                'image' => UploadedFile::fake()->image('menu.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error', function (string $message): bool {
                return str_contains(strtolower($message), 'quota');
            });
    }
}
