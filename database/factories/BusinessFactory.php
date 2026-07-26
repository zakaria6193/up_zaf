<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_user_id' => BusinessUser::factory(),
            'name' => fake()->company(),
            'address' => fake()->address(),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
            'color' => fake()->hexColor(),
            'currency' => 'MAD',
            'qr_style' => 'pulse',
            'is_active' => true,
            'seo_title' => fake()->sentence(),
            'seo_description' => fake()->paragraph(),
            'seo_keywords' => implode(', ', fake()->words(5)),
        ];
    }

    /**
     * Indicate that the business is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the business has no location data.
     */
    public function withoutLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => null,
            'lat' => null,
            'lng' => null,
        ]);
    }
}
